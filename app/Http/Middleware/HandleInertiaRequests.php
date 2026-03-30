<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'admin-builder';

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'auth' => [
                'user' => $request->user()?->only(['id', 'name', 'email']),
            ],
            'cmsShell' => [
                'brand' => 'Struktura CMS',
                'product' => 'Core Engine',
                'navigation' => [
                    [
                        'key' => 'dashboard-engine',
                        'label' => 'Dashboard Engine',
                        'description' => 'Model pipelines, KPIs, and renderer health.',
                        'href' => route('admin.dashboard.builder'),
                        'icon' => 'dashboard',
                        'section' => 'Engine',
                        'active' => $request->routeIs('admin.dashboard.builder'),
                    ],
                    [
                        'key' => 'page-builder',
                        'label' => 'Page Builder',
                        'description' => 'Compose schema-driven page documents.',
                        'href' => route('admin.pages.builder.create'),
                        'icon' => 'page',
                        'section' => 'Workspaces',
                        'active' => $request->routeIs('admin.pages.builder.*'),
                    ],
                    [
                        'key' => 'navigation-builder',
                        'label' => 'Navigation Builder',
                        'description' => 'Model tree structures and targeting rules.',
                        'href' => route('admin.navigation.builder.edit'),
                        'icon' => 'navigation',
                        'section' => 'Workspaces',
                        'active' => $request->routeIs('admin.navigation.builder.*'),
                    ],
                    [
                        'key' => 'component-definitions',
                        'label' => 'Component Definitions',
                        'description' => 'Schema catalogs, defaults, and surfaces.',
                        'href' => route('filament.admin.resources.component-definitions.index'),
                        'icon' => 'component',
                        'section' => 'Modeling',
                        'active' => $request->routeIs('filament.admin.resources.component-definitions.*'),
                    ],
                    [
                        'key' => 'settings',
                        'label' => 'Settings',
                        'description' => 'Environment, access rules, and global controls.',
                        'href' => url('/admin'),
                        'icon' => 'settings',
                        'section' => 'Modeling',
                        'active' => $request->is('admin'),
                    ],
                ],
            ],
        ]);
    }
}
