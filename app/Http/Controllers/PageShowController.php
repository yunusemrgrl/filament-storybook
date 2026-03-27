<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Contracts\View\View;

class PageShowController extends Controller
{
    public function __invoke(string $slug): View
    {
        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('pages.show', [
            'page' => $page,
            'resolvedBlocks' => $page->blocks->resolve(),
        ]);
    }
}
