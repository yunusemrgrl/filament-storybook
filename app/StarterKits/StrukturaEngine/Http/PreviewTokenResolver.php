<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Http;

use Illuminate\Http\Request;

final class PreviewTokenResolver
{
    /**
     * @return array{title: string, nodes: array<int, array<string, mixed>>}|null
     */
    public function resolve(Request $request, string $expectedSlug): ?array
    {
        $previewToken = $request->query('preview_token');

        if (! is_string($previewToken) || trim($previewToken) === '') {
            return null;
        }

        $preview = session()->get("page-builder.preview.{$previewToken}");

        if (! is_array($preview) || (($preview['slug'] ?? null) !== $expectedSlug)) {
            return null;
        }

        $nodes = $preview['nodes'] ?? $preview['blocks'] ?? [];

        if (! is_array($nodes)) {
            return null;
        }

        $title = is_string($preview['title'] ?? null) && trim((string) $preview['title']) !== ''
            ? trim((string) $preview['title'])
            : 'Preview page';

        return [
            'title' => $title,
            'nodes' => $nodes,
        ];
    }
}
