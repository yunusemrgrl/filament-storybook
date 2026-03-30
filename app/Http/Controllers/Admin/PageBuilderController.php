<?php

namespace App\Http\Controllers\Admin;

use App\ComponentSurface;
use App\Filament\Storybook\Blocks\BlockRegistry;
use App\Http\Controllers\Controller;
use App\Http\Requests\SavePageBuilderRequest;
use App\Models\Page;
use App\PageStatus;
use App\StarterKits\StrukturaEngine\Models\ModelDescriptor;
use App\StarterKits\StrukturaEngine\Services\ModelIntrospector;
use App\StarterKits\StrukturaEngine\Services\ModelRegistry;
use App\Support\PageBuilder\EditorStateMapper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class PageBuilderController extends Controller
{
    public function __construct(
        private readonly EditorStateMapper $editorStateMapper,
        private readonly ModelRegistry $modelRegistry,
        private readonly ModelIntrospector $modelIntrospector,
    ) {}

    public function create(): Response
    {
        return Inertia::render('PageBuilder', $this->pageProps());
    }

    public function edit(Page $page): Response
    {
        return Inertia::render('PageBuilder', $this->pageProps($page));
    }

    public function store(SavePageBuilderRequest $request): RedirectResponse
    {
        $page = Page::query()->create([
            'title' => $request->string('title')->value(),
            'slug' => $request->string('slug')->value(),
            'status' => $request->string('status')->value(),
            'blocks' => $this->editorStateMapper->toPersistedPayload(
                ComponentSurface::Page,
                $request->input('nodes', []),
            ),
        ]);

        return to_route('admin.pages.builder.edit', $page);
    }

    public function update(SavePageBuilderRequest $request, Page $page): RedirectResponse
    {
        $page->update([
            'title' => $request->string('title')->value(),
            'slug' => $request->string('slug')->value(),
            'status' => $request->string('status')->value(),
            'blocks' => $this->editorStateMapper->toPersistedPayload(
                ComponentSurface::Page,
                $request->input('nodes', []),
            ),
        ]);

        return to_route('admin.pages.builder.edit', $page);
    }

    /**
     * @return array<string, mixed>
     */
    private function pageProps(?Page $page = null): array
    {
        return [
            'surface' => ComponentSurface::Page->value,
            'page' => [
                'id' => $page?->getKey(),
                'title' => $page?->title ?? '',
                'slug' => $page?->slug ?? '',
                'status' => ($page?->status ?? PageStatus::Draft)->value,
                'nodes' => $this->editorStateMapper->toEditorNodes(
                    ComponentSurface::Page,
                    $page?->blocks ?? [],
                ),
            ],
            'definitions' => array_values(array_filter(
                BlockRegistry::schemasForSurface(ComponentSurface::Page),
                static fn (array $schema): bool => ($schema['source'] ?? null) === 'definition',
            )),
            'dataBinding' => $this->dataBindingProps(ComponentSurface::Page),
            'routes' => [
                'index' => route('filament.admin.resources.pages.index'),
                'store' => route('admin.pages.builder.store'),
                'upload' => route('admin.pages.builder.upload'),
                'update' => $page ? route('admin.pages.builder.update', $page) : null,
                'publicPreview' => $page && $page->status->isPublished()
                    ? route('pages.show', $page->slug)
                    : null,
            ],
        ];
    }

    /**
     * @return array{
     *     models: array<int, array<string, mixed>>,
     *     relationshipsByModel: array<string, array<int, array<string, mixed>>>,
     *     columnsByModel: array<string, array<int, array<string, mixed>>>
     * }
     */
    private function dataBindingProps(ComponentSurface $surface): array
    {
        $models = $this->modelRegistry->models($surface);
        $descriptors = collect($models)
            ->mapWithKeys(function (array $model): array {
                $descriptor = $this->modelIntrospector->describe($model['class']);

                return [$model['class'] => $descriptor];
            });

        $relatedModels = $descriptors
            ->flatMap(function ($descriptor): array {
                return array_map(
                    static fn (array $relationship): string => $relationship['relatedModel'],
                    $descriptor->toArray()['relationships'],
                );
            })
            ->filter(fn (string $modelClass): bool => ! $descriptors->has($modelClass) && $this->modelRegistry->isAllowed($modelClass))
            ->unique()
            ->values();

        /** @var Collection<string, ModelDescriptor> $allDescriptors */
        $allDescriptors = $descriptors;

        foreach ($relatedModels as $relatedModel) {
            $allDescriptors->put($relatedModel, $this->modelIntrospector->describe($relatedModel));
        }

        return [
            'models' => $models,
            'relationshipsByModel' => $descriptors
                ->mapWithKeys(static fn ($descriptor, string $modelClass): array => [
                    $modelClass => array_map(
                        static fn ($relationship): array => $relationship->toArray(),
                        $descriptor->relationships,
                    ),
                ])
                ->all(),
            'columnsByModel' => $allDescriptors
                ->mapWithKeys(static fn ($descriptor, string $modelClass): array => [
                    $modelClass => array_map(
                        static fn ($column): array => $column->toArray(),
                        $descriptor->columns,
                    ),
                ])
                ->all(),
        ];
    }
}
