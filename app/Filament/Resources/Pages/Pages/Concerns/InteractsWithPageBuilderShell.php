<?php

namespace App\Filament\Resources\Pages\Pages\Concerns;

use App\ComponentSurface;
use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\BlockCollection;
use App\Filament\Storybook\Blocks\BlockRegistry;
use App\Filament\Storybook\Blocks\ResolvedBlock;
use App\Filament\Storybook\KnobDefinition;
use App\Filament\Storybook\Support\KnobSchemaCompiler;
use App\PageStatus;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait InteractsWithPageBuilderShell
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public array $editorBlocks = [];

    public ?string $selectedBlockUuid = null;

    public string $paletteSearch = '';

    public string $previewToken = '';

    public int $previewVersion = 0;

    /**
     * @param  array<int, array<string, mixed>>  $payloads
     */
    protected function bootPageBuilderShell(array $payloads = []): void
    {
        $this->editorBlocks = array_map(
            fn (array $payload): array => $this->makeEditorBlock($payload),
            array_values($payloads),
        );

        $this->selectedBlockUuid = $this->editorBlocks[0]['uuid'] ?? null;

        $this->syncPagePreview();
    }

    public function updated(string $name, mixed $value): void
    {
        if (
            str_starts_with($name, 'data.')
            || str_starts_with($name, 'editorBlocks')
        ) {
            $this->syncPagePreview();
        }
    }

    public function addBlock(string $type): void
    {
        $story = BlockRegistry::findCmsByTypeForSurface(ComponentSurface::Page, $type);

        if (! $story) {
            return;
        }

        $block = $this->makeEditorBlock($story->getPresetPayload('default'));

        $this->editorBlocks[] = $block;
        $this->selectedBlockUuid = $block['uuid'];

        $this->syncPagePreview();
    }

    public function selectBlock(string $uuid): void
    {
        if (! collect($this->editorBlocks)->contains(fn (array $block): bool => $block['uuid'] === $uuid)) {
            return;
        }

        $this->selectedBlockUuid = $uuid;
    }

    public function moveSelectedBlockUp(): void
    {
        $index = $this->getSelectedBlockIndex();

        if (($index === null) || ($index === 0)) {
            return;
        }

        $blocks = $this->editorBlocks;
        [$blocks[$index - 1], $blocks[$index]] = [$blocks[$index], $blocks[$index - 1]];

        $this->editorBlocks = array_values($blocks);

        $this->syncPagePreview();
    }

    public function moveSelectedBlockDown(): void
    {
        $index = $this->getSelectedBlockIndex();

        if (($index === null) || ($index >= (count($this->editorBlocks) - 1))) {
            return;
        }

        $blocks = $this->editorBlocks;
        [$blocks[$index + 1], $blocks[$index]] = [$blocks[$index], $blocks[$index + 1]];

        $this->editorBlocks = array_values($blocks);

        $this->syncPagePreview();
    }

    public function duplicateSelectedBlock(): void
    {
        $index = $this->getSelectedBlockIndex();
        $selectedBlock = $this->getSelectedEditorBlock();

        if (($index === null) || ($selectedBlock === null)) {
            return;
        }

        $duplicateBlock = $this->makeEditorBlock(Arr::except($selectedBlock, ['uuid']));
        $blocks = $this->editorBlocks;

        array_splice($blocks, $index + 1, 0, [$duplicateBlock]);

        $this->editorBlocks = array_values($blocks);
        $this->selectedBlockUuid = $duplicateBlock['uuid'];

        $this->syncPagePreview();
    }

    public function removeSelectedBlock(): void
    {
        $index = $this->getSelectedBlockIndex();

        if ($index === null) {
            return;
        }

        $blocks = $this->editorBlocks;

        array_splice($blocks, $index, 1);

        $this->editorBlocks = array_values($blocks);
        $this->selectedBlockUuid = $this->editorBlocks[$index]['uuid']
            ?? $this->editorBlocks[$index - 1]['uuid']
            ?? null;

        $this->syncPagePreview();
    }

    public function editBlockAction(): Action
    {
        return Action::make('editBlock')
            ->label('Edit block')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->slideOver()
            ->modalWidth('5xl')
            ->modalHeading(fn (): string => $this->getSelectedBlockTitle('Edit block'))
            ->modalDescription(fn (): string => $this->getSelectedBlockDescription())
            ->modalSubmitActionLabel('Apply changes')
            ->schema(fn (): array => $this->getSelectedBlockSchema())
            ->fillForm(fn (): array => $this->getSelectedBlockBuilderData())
            ->action(function (array $data): void {
                $selectedBlock = $this->getSelectedEditorBlock();
                $story = $this->getSelectedBlockStory();
                $index = $this->getSelectedBlockIndex();

                if (($selectedBlock === null) || ($story === null) || ($index === null)) {
                    return;
                }

                $variant = is_string($selectedBlock['variant'] ?? null)
                    ? $selectedBlock['variant']
                    : 'default';

                $normalizedData = $this->normalizeActionDataForStory($story, $data);

                $this->editorBlocks[$index] = [
                    'uuid' => $selectedBlock['uuid'],
                    ...$story->makeBlockPayload($normalizedData, $variant),
                ];

                $this->syncPagePreview();
            });
    }

    public function getEditorCanvasBlocks(): array
    {
        $resolvedBlocks = $this->resolveEditorBlocks();

        return collect(array_values($this->editorBlocks))
            ->map(function (array $block, int $index) use ($resolvedBlocks): ?array {
                $resolved = $resolvedBlocks[$index] ?? null;

                if (! $resolved instanceof ResolvedBlock) {
                    return null;
                }

                return [
                    'uuid' => $block['uuid'],
                    'position' => $index + 1,
                    'isSelected' => $block['uuid'] === $this->selectedBlockUuid,
                    'label' => $resolved->story->getBuilderItemLabel(
                        $resolved->story->makeBuilderData(Arr::except($block, ['uuid'])),
                    ),
                    'resolved' => $resolved,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getEditorStructureBlocks(): array
    {
        return collect(array_values($this->editorBlocks))
            ->map(function (array $block, int $index): ?array {
                $type = $block['type'] ?? null;

                if (! is_string($type) || ($type === '')) {
                    return null;
                }

                $story = BlockRegistry::findCmsByTypeForSurface(ComponentSurface::Page, $type);

                if (! $story) {
                    return null;
                }

                return [
                    'uuid' => $block['uuid'] ?? null,
                    'position' => $index + 1,
                    'isSelected' => ($block['uuid'] ?? null) === $this->selectedBlockUuid,
                    'title' => $story->getBuilderItemLabel(
                        $story->makeBuilderData(Arr::except($block, ['uuid'])),
                    ),
                    'group' => $story->group,
                    'type' => $type,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getPaletteGroups(): array
    {
        $search = Str::lower(trim($this->paletteSearch));

        return collect(BlockRegistry::cmsForSurface(ComponentSurface::Page))
            ->sortBy(fn (AbstractBlockStory $story): string => "{$story->group}.{$story->title}")
            ->filter(function (AbstractBlockStory $story) use ($search): bool {
                if ($search === '') {
                    return true;
                }

                return str_contains(Str::lower($story->title), $search)
                    || str_contains(Str::lower($story->description), $search)
                    || str_contains(Str::lower($story->group), $search);
            })
            ->groupBy(fn (AbstractBlockStory $story): string => trim($story->group) !== '' ? $story->group : 'General')
            ->map(function (Collection $stories, string $group): array {
                return [
                    'group' => $group,
                    'items' => $stories->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function hasSelectedBlock(): bool
    {
        return $this->getSelectedEditorBlock() !== null;
    }

    public function getSelectedBlockTitle(string $fallback = 'Select a block'): string
    {
        $selectedBlock = $this->getSelectedEditorBlock();
        $story = $this->getSelectedBlockStory();

        if (($selectedBlock === null) || ($story === null)) {
            return $fallback;
        }

        return $story->getBuilderItemLabel(
            $story->makeBuilderData(Arr::except($selectedBlock, ['uuid'])),
        );
    }

    public function getSelectedBlockDescription(): string
    {
        return $this->getSelectedBlockStory()?->description
            ?? 'Select a block from the canvas to inspect and edit its schema-driven props.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getSelectedBlockMeta(): array
    {
        $selectedBlock = $this->getSelectedEditorBlock();
        $story = $this->getSelectedBlockStory();
        $position = $this->getSelectedBlockIndex();

        if (($selectedBlock === null) || ($story === null) || ($position === null)) {
            return [
                'type' => null,
                'group' => null,
                'position' => null,
                'total' => count($this->editorBlocks),
                'fields' => 0,
                'keys' => [],
            ];
        }

        $payload = Arr::except($selectedBlock, ['uuid']);
        $fieldKeys = $payload['props'] ?? [];

        if (! is_array($fieldKeys)) {
            $fieldKeys = [];
        }

        return [
            'type' => $payload['type'] ?? null,
            'group' => $story->group,
            'position' => $position + 1,
            'total' => count($this->editorBlocks),
            'fields' => count($story->knobs()),
            'keys' => array_keys($fieldKeys),
        ];
    }

    public function getPagePreviewUrl(): string
    {
        $this->ensurePreviewToken();

        return route('admin.pages.preview', [
            'token' => $this->previewToken,
            'v' => $this->previewVersion,
        ]);
    }

    public function getEditorRouteLabel(): string
    {
        return '/'.$this->getPreviewSlug();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getPersistedEditorBlocks(): array
    {
        return array_map(
            static fn (array $block): array => Arr::except($block, ['uuid']),
            array_values($this->editorBlocks),
        );
    }

    protected function syncPagePreview(): void
    {
        $this->ensurePreviewToken();

        session()->put($this->getPreviewSessionKey(), [
            'title' => $this->getPreviewDisplayTitle(),
            'slug' => $this->getPreviewSlug(),
            'status' => $this->getPreviewStatusValue(),
            'blocks' => $this->getPersistedEditorBlocks(),
        ]);

        $this->previewVersion++;
    }

    protected function getPreviewDisplayTitle(): string
    {
        $title = trim((string) ($this->data['title'] ?? ''));

        return $title !== '' ? $title : 'Untitled page';
    }

    protected function getPreviewSlug(): string
    {
        $slug = trim((string) ($this->data['slug'] ?? ''));

        return $slug !== '' ? $slug : 'preview-page';
    }

    protected function getPreviewStatusValue(): string
    {
        $status = trim((string) ($this->data['status'] ?? ''));

        return array_key_exists($status, PageStatus::options()) ? $status : PageStatus::Draft->value;
    }

    protected function ensurePreviewToken(): void
    {
        if ($this->previewToken !== '') {
            return;
        }

        $this->previewToken = (string) Str::uuid();
    }

    protected function getPreviewSessionKey(): string
    {
        $this->ensurePreviewToken();

        return "page-builder.preview.{$this->previewToken}";
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSelectedBlockSchema(): array
    {
        $story = $this->getSelectedBlockStory();

        if (! $story) {
            return [];
        }

        return app(KnobSchemaCompiler::class)->compile(
            $story->knobs(),
            live: true,
            testIdPrefix: 'editor-field',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getSelectedBlockBuilderData(): array
    {
        $selectedBlock = $this->getSelectedEditorBlock();
        $story = $this->getSelectedBlockStory();

        if (($selectedBlock === null) || ($story === null)) {
            return [];
        }

        return $story->makeBuilderData(Arr::except($selectedBlock, ['uuid']));
    }

    private function getSelectedBlockStory(): ?AbstractBlockStory
    {
        $selectedBlock = $this->getSelectedEditorBlock();
        $type = $selectedBlock['type'] ?? null;

        if (! is_string($type) || ($type === '')) {
            return null;
        }

        return BlockRegistry::findCmsByTypeForSurface(ComponentSurface::Page, $type);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getSelectedEditorBlock(): ?array
    {
        if ($this->selectedBlockUuid === null) {
            return null;
        }

        foreach ($this->editorBlocks as $block) {
            if (($block['uuid'] ?? null) === $this->selectedBlockUuid) {
                return $block;
            }
        }

        return null;
    }

    private function getSelectedBlockIndex(): ?int
    {
        if ($this->selectedBlockUuid === null) {
            return null;
        }

        foreach (array_values($this->editorBlocks) as $index => $block) {
            if (($block['uuid'] ?? null) === $this->selectedBlockUuid) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @return array<int, ResolvedBlock>
     */
    private function resolveEditorBlocks(): array
    {
        try {
            return BlockCollection::fromArray($this->getPersistedEditorBlocks())->resolve();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function makeEditorBlock(array $payload): array
    {
        $uuid = is_string($payload['uuid'] ?? null) && (trim((string) $payload['uuid']) !== '')
            ? trim((string) $payload['uuid'])
            : (string) Str::uuid();

        unset($payload['uuid']);

        return [
            'uuid' => $uuid,
            ...$payload,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeActionDataForStory(AbstractBlockStory $story, array $data): array
    {
        return $this->normalizeKnobValues($story->knobs(), $data);
    }

    /**
     * @param  array<int, KnobDefinition>  $definitions
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function normalizeKnobValues(array $definitions, array $values): array
    {
        $normalized = $values;

        foreach ($definitions as $definition) {
            $name = $definition->getName();
            $value = $values[$name] ?? null;

            $normalized[$name] = match ($definition->getType()) {
                KnobDefinition::TYPE_FILE => $this->normalizeFileKnobValue($definition, $value),
                KnobDefinition::TYPE_REPEATER => $this->normalizeRepeaterKnobValue($definition, $value),
                default => $value,
            };
        }

        return $normalized;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRepeaterKnobValue(KnobDefinition $definition, mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_map(
            fn (mixed $item): array => $this->normalizeKnobValues(
                $definition->getRepeaterSchema(),
                is_array($item) ? $item : [],
            ),
            array_filter($value, 'is_array'),
        ));
    }

    private function normalizeFileKnobValue(KnobDefinition $definition, mixed $value): ?string
    {
        $candidate = $this->extractFileCandidate($value);

        if ($candidate instanceof TemporaryUploadedFile || $candidate instanceof UploadedFile) {
            return $candidate->storePublicly(
                $definition->getFileDirectory() ?? 'page-builder/uploads',
                $definition->getFileDisk() ?? 'public',
            );
        }

        if (! is_string($candidate)) {
            return null;
        }

        $candidate = trim($candidate);

        return $candidate !== '' ? $candidate : null;
    }

    private function extractFileCandidate(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $item) {
            if ($item instanceof TemporaryUploadedFile || $item instanceof UploadedFile) {
                return $item;
            }

            if (is_string($item) && trim($item) !== '') {
                return $item;
            }
        }

        return null;
    }
}
