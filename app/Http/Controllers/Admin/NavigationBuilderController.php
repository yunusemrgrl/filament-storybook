<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveNavigationBuilderRequest;
use App\Models\NavigationMenu;
use App\Support\PageBuilder\NavigationBuilderRegistry;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NavigationBuilderController extends Controller
{
    public function edit(NavigationBuilderRegistry $registry): Response
    {
        $menu = $this->resolveMenu();

        return Inertia::render('NavigationBuilder', [
            'navigation' => [
                'name' => $menu->name,
                'placement' => $menu->placement,
                'channel' => $menu->channel,
            ],
            'templates' => $registry->templates(),
            'initialTree' => $this->initialTree($menu, $registry),
            'routes' => [
                'update' => route('admin.navigation.builder.update'),
            ],
        ]);
    }

    public function update(SaveNavigationBuilderRequest $request): RedirectResponse
    {
        $menu = $this->resolveMenu();

        $menu->update([
            'name' => $request->string('name')->value(),
            'placement' => $request->string('placement')->value(),
            'channel' => $request->string('channel')->value(),
            'nodes' => $request->input('nodes', []),
            'draft_nodes' => $request->input('nodes', []),
            'is_active' => true,
        ]);

        return to_route('admin.navigation.builder.edit');
    }

    private function resolveMenu(): NavigationMenu
    {
        return NavigationMenu::query()->firstOrCreate(
            ['key' => (string) config('struktura-engine.navigation.menu_key', 'admin-sidebar')],
            [
                'name' => 'Admin Sidebar',
                'placement' => 'Sidebar',
                'channel' => 'Admin',
                'nodes' => [],
                'draft_nodes' => [],
                'is_active' => true,
            ],
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function initialTree(NavigationMenu $menu, NavigationBuilderRegistry $registry): array
    {
        $draftNodes = $menu->draftNodes();

        if ($draftNodes !== []) {
            return $draftNodes;
        }

        $publishedNodes = $menu->publishedNodes();

        if ($publishedNodes !== []) {
            return $publishedNodes;
        }

        return $registry->tree();
    }
}
