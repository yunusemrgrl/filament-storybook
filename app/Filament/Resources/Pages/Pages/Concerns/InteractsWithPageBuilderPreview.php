<?php

namespace App\Filament\Resources\Pages\Pages\Concerns;

use App\Filament\Storybook\Blocks\BlockCollection;
use App\Filament\Storybook\Blocks\BuilderStateMapper;
use App\PageStatus;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

trait InteractsWithPageBuilderPreview
{
    public string $previewToken = '';

    public int $previewVersion = 0;

    protected function afterFill(): void
    {
        $this->syncPagePreview();
    }

    public function updated(string $name, mixed $value): void
    {
        if (! str_starts_with($name, 'data.')) {
            return;
        }

        $this->syncPagePreview();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])
                    ->schema([
                        Group::make([
                            $this->getFormContentComponent(),
                        ])
                            ->columnSpan([
                                'xl' => 5,
                            ]),
                        View::make('filament.resources.pages.partials.page-builder-preview')
                            ->viewData(fn (): array => [
                                'previewFrameUrl' => $this->getPagePreviewUrl(),
                                'previewTitle' => $this->getPreviewDisplayTitle(),
                                'previewStatusLabel' => $this->getPreviewStatusLabel(),
                                'previewSlug' => $this->getPreviewSlug(),
                                'previewVersion' => $this->previewVersion,
                            ])
                            ->columnSpan([
                                'xl' => 7,
                            ]),
                    ]),
            ]);
    }

    public function getPagePreviewUrl(): string
    {
        $this->ensurePreviewToken();

        return route('admin.pages.preview', [
            'token' => $this->previewToken,
            'v' => $this->previewVersion,
        ]);
    }

    protected function syncPagePreview(): void
    {
        $this->ensurePreviewToken();

        session()->put($this->getPreviewSessionKey(), $this->buildPreviewPayload());

        $this->previewVersion++;
    }

    /**
     * @return array{title: string, slug: string, status: string, blocks: array<int, array<string, mixed>>}
     */
    protected function buildPreviewPayload(): array
    {
        $data = is_array($this->data ?? null) ? $this->data : [];

        return [
            'title' => $this->getPreviewDisplayTitle(),
            'slug' => $this->getPreviewSlug(),
            'status' => $this->getPreviewStatusValue(),
            'blocks' => $this->resolvePreviewBlocks($data['builderBlocks'] ?? [])->toArray(),
        ];
    }

    protected function getPreviewDisplayTitle(): string
    {
        $title = trim((string) (is_array($this->data ?? null) ? ($this->data['title'] ?? '') : ''));

        return $title !== '' ? $title : 'Untitled page';
    }

    protected function getPreviewSlug(): string
    {
        $slug = trim((string) (is_array($this->data ?? null) ? ($this->data['slug'] ?? '') : ''));

        return $slug !== '' ? $slug : 'preview-page';
    }

    protected function getPreviewStatusValue(): string
    {
        $status = trim((string) (is_array($this->data ?? null) ? ($this->data['status'] ?? '') : ''));

        return array_key_exists($status, PageStatus::options()) ? $status : PageStatus::Draft->value;
    }

    protected function getPreviewStatusLabel(): string
    {
        return PageStatus::options()[$this->getPreviewStatusValue()] ?? 'Draft';
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

    protected function resolvePreviewBlocks(mixed $builderBlocks): BlockCollection
    {
        if (! is_array($builderBlocks)) {
            return BlockCollection::empty();
        }

        try {
            return app(BuilderStateMapper::class)->fromBuilderState($builderBlocks);
        } catch (\Throwable) {
            return BlockCollection::empty();
        }
    }
}
