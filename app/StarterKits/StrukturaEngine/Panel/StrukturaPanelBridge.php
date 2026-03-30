<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Panel;

use App\Models\NavigationMenu;
use App\Models\Page;
use App\StarterKits\StrukturaEngine\Compilers\NavigationCompiler;
use App\StarterKits\StrukturaEngine\Compilers\PageCompiler;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Schema;

class StrukturaPanelBridge
{
    public function __construct(
        private readonly NavigationCompiler $navigationCompiler,
        private readonly PageCompiler $pageCompiler,
    ) {}

    public function enabled(): bool
    {
        return (bool) config('struktura-engine.enabled', false);
    }

    /**
     * @return array<int, NavigationGroup>
     */
    public function navigationGroups(): array
    {
        if (! $this->enabled()) {
            return [];
        }

        return $this->compiledNavigation()['groups'];
    }

    /**
     * @return array<int, NavigationItem>
     */
    public function navigationItems(): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $compiledNavigation = $this->compiledNavigation();
        $groupedItems = [];

        foreach ($compiledNavigation['groups'] as $group) {
            $groupedItems = [
                ...$groupedItems,
                ...$this->assignGroupToItems($group->getLabel(), $group->getItems()),
            ];
        }

        return [
            ...$compiledNavigation['items'],
            ...$groupedItems,
        ];
    }

    public function buildNavigation(NavigationBuilder $builder, Panel $panel): NavigationBuilder
    {
        return $builder
            ->groups($this->navigationGroups())
            ->items([
                ...$this->defaultNavigationItems($panel),
                ...$this->navigationItems(),
            ]);
    }

    /**
     * @return array<int, WidgetConfiguration>
     */
    public function widgets(): array
    {
        if (! $this->enabled() || (! Schema::hasTable('pages'))) {
            return [];
        }

        $page = Page::query()
            ->published()
            ->where('slug', (string) config('struktura-engine.dashboard.widget_page_slug', 'system-analytics'))
            ->first();

        if (! $page) {
            return [];
        }

        return $this->pageCompiler->compileWidgetConfigurations($page);
    }

    private function activeNavigationMenu(): ?NavigationMenu
    {
        if (! Schema::hasTable('navigation_menus')) {
            return null;
        }

        return NavigationMenu::query()
            ->active()
            ->key((string) config('struktura-engine.navigation.menu_key', 'admin-sidebar'))
            ->first();
    }

    /**
     * @return array{groups: array<int, NavigationGroup>, items: array<int, NavigationItem>}
     */
    private function compiledNavigation(): array
    {
        $menu = $this->activeNavigationMenu();

        if (! $menu) {
            return [
                'groups' => [],
                'items' => [],
            ];
        }

        return $this->navigationCompiler->compile($menu->publishedNodes());
    }

    /**
     * @return array<int, NavigationItem>
     */
    private function defaultNavigationItems(Panel $panel): array
    {
        $items = [];

        foreach ($panel->getPages() as $pageClass) {
            if (filled($pageClass::getCluster())) {
                continue;
            }

            if (! $pageClass::shouldRegisterNavigation() || ! $pageClass::canAccess()) {
                continue;
            }

            $items = [
                ...$items,
                ...$pageClass::getNavigationItems(),
            ];
        }

        foreach ($panel->getResources() as $resourceClass) {
            if (filled($resourceClass::getCluster())) {
                continue;
            }

            if (! $resourceClass::shouldRegisterNavigation() || ! $resourceClass::canAccess()) {
                continue;
            }

            if ($resourceClass::getParentResourceRegistration()) {
                continue;
            }

            $items = [
                ...$items,
                ...$resourceClass::getNavigationItems(),
            ];
        }

        return $items;
    }

    /**
     * @param  array<int, NavigationItem>|Arrayable<int, NavigationItem>  $items
     * @return array<int, NavigationItem>
     */
    private function assignGroupToItems(?string $groupLabel, array|Arrayable $items): array
    {
        return collect($items)
            ->map(function (NavigationItem $item) use ($groupLabel): NavigationItem {
                $navigationItem = clone $item;

                if (filled($groupLabel)) {
                    $navigationItem->group($groupLabel);
                }

                $childItems = $navigationItem->getChildItems();

                if ($childItems !== []) {
                    $navigationItem->childItems($this->cloneNavigationItems($childItems));
                }

                return $navigationItem;
            })
            ->all();
    }

    /**
     * @param  array<int, NavigationItem>|Arrayable<int, NavigationItem>  $items
     * @return array<int, NavigationItem>
     */
    private function cloneNavigationItems(array|Arrayable $items): array
    {
        return collect($items)
            ->map(function (NavigationItem $item): NavigationItem {
                $navigationItem = clone $item;

                $childItems = $navigationItem->getChildItems();

                if ($childItems !== []) {
                    $navigationItem->childItems($this->cloneNavigationItems($childItems));
                }

                return $navigationItem;
            })
            ->all();
    }
}
