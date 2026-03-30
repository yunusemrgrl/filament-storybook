<?php

namespace App\Http\Controllers;

use App\ComponentSurface;
use App\StarterKits\StrukturaEngine\Services\EngineCompilerRuntime;
use Illuminate\Contracts\View\View;

class PagePreviewController extends Controller
{
    public function __invoke(string $token, EngineCompilerRuntime $compilerRuntime): View
    {
        $preview = session()->get("page-builder.preview.{$token}");

        if (! is_array($preview)) {
            $preview = [];
        }

        $nodes = $preview['nodes'] ?? $preview['blocks'] ?? [];

        if (! is_array($nodes)) {
            $nodes = [];
        }

        try {
            $compiledNodes = $compilerRuntime->compile(ComponentSurface::Page, $nodes, 'preview');
        } catch (\Throwable) {
            $compiledNodes = [];
        }

        return view('pages.preview', [
            'title' => is_string($preview['title'] ?? null) ? $preview['title'] : 'Untitled page',
            'compiledNodes' => $compiledNodes,
        ]);
    }
}
