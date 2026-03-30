<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Page as StrukturaPage;
use App\StarterKits\StrukturaEngine\Compilers\PageCompiler;
use App\StarterKits\StrukturaEngine\Http\PreviewTokenResolver;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class DynamicStrukturaPage extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'struktura/{pageSlug}';

    /**
     * @var array<int, Component>
     */
    protected array $compiledComponents = [];

    public string $pageTitle = 'Dynamic Struktura Page';

    public string $resolvedSlug = '';

    public function mount(string $pageSlug): void
    {
        /** @var Request $request */
        $request = request();
        /** @var PageCompiler $pageCompiler */
        $pageCompiler = app(PageCompiler::class);
        $page = $this->resolvePage($pageSlug, $request);
        $preview = $this->previewPayload($request, $pageSlug);
        $nodes = $preview['nodes'] ?? null;
        $title = $preview['title'] ?? $page->title;

        $this->resolvedSlug = $page->slug;
        $this->pageTitle = $title;
        $this->compiledComponents = $pageCompiler->compileContentComponents(
            $page,
            $nodes,
            $preview ? 'preview' : 'runtime',
        );
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components($this->compiledComponents);
    }

    public function getTitle(): string
    {
        return $this->pageTitle;
    }

    private function resolvePage(string $pageSlug, Request $request): StrukturaPage
    {
        $preview = $this->previewPayload($request, $pageSlug);

        $query = StrukturaPage::query()->where('slug', $pageSlug);

        if (! $preview) {
            $query->published();
        }

        return $query->firstOr(function () use ($pageSlug): never {
            throw (new ModelNotFoundException)->setModel(StrukturaPage::class, [$pageSlug]);
        });
    }

    /**
     * @return array{title: string, nodes: array<int, array<string, mixed>>}|null
     */
    private function previewPayload(Request $request, string $pageSlug): ?array
    {
        return app(PreviewTokenResolver::class)->resolve($request, $pageSlug);
    }
}
