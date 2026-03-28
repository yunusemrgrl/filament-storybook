<?php

namespace App\Http\Controllers\Admin;

use App\ComponentSurface;
use App\Filament\Storybook\Blocks\BlockRegistry;
use App\Http\Controllers\Controller;
use App\Http\Requests\SavePageBuilderRequest;
use App\Models\Page;
use App\PageStatus;
use App\Support\PageBuilder\EditorStateMapper;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PageBuilderController extends Controller
{
    public function __construct(
        private readonly EditorStateMapper $editorStateMapper,
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
                $request->validated('blocks', []),
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
                $request->validated('blocks', []),
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
                'blocks' => $this->editorStateMapper->toEditorBlocks(
                    ComponentSurface::Page,
                    $page?->blocks ?? [],
                ),
            ],
            'availableBlocks' => BlockRegistry::schemasForSurface(ComponentSurface::Page),
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
}
