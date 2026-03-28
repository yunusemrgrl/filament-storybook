<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PageBuilder\DashboardWidgetRegistry;
use Inertia\Inertia;
use Inertia\Response;

class DashboardBuilderController extends Controller
{
    public function __invoke(DashboardWidgetRegistry $registry): Response
    {
        return Inertia::render('DashboardBuilder', [
            'widgets' => $registry->all(),
            'initialCanvas' => $registry->defaultCanvas(),
        ]);
    }
}
