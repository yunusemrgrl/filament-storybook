<?php

namespace App\Http\Controllers;

use App\Filament\Storybook\Blocks\BlockCollection;
use Illuminate\Contracts\View\View;

class PagePreviewController extends Controller
{
    public function __invoke(string $token): View
    {
        $preview = session()->get("page-builder.preview.{$token}");

        if (! is_array($preview)) {
            $preview = [];
        }

        $blocks = $preview['blocks'] ?? [];

        if (! is_array($blocks)) {
            $blocks = [];
        }

        try {
            $resolvedBlocks = BlockCollection::fromArray($blocks)->resolve();
        } catch (\Throwable) {
            $resolvedBlocks = [];
        }

        return view('pages.preview', [
            'title' => is_string($preview['title'] ?? null) ? $preview['title'] : 'Untitled page',
            'resolvedBlocks' => $resolvedBlocks,
        ]);
    }
}
