<?php

namespace App\Http\Controllers;

use App\ComponentSurface;
use App\Models\Page;
use App\StarterKits\StrukturaEngine\Services\EngineCompilerRuntime;
use Illuminate\Contracts\View\View;

class PageShowController extends Controller
{
    public function __invoke(string $slug, EngineCompilerRuntime $compilerRuntime): View
    {
        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('pages.show', [
            'page' => $page,
            'compiledNodes' => $compilerRuntime->compile(ComponentSurface::Page, $page->blocks),
        ]);
    }
}
