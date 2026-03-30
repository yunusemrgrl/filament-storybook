<?php

declare(strict_types=1);

namespace App\StarterKits\StrukturaEngine\Compilers;

use App\StarterKits\StrukturaEngine\Contracts\CompilesNavigationAst;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Http\Request;

readonly class NavigationCompiler implements CompilesNavigationAst
{
    /**
     * @param  array<int, array<string, mixed>>  $ast
     * @return array{groups: array<int, NavigationGroup>, items: array<int, NavigationItem>}
     */
    public function compile(array $ast): array
    {
        $groupedItems = [];
        $ungroupedItems = [];

        foreach ($ast as $node) {
            if (! is_array($node)) {
                continue;
            }

            $item = $this->compileItem($node);

            if (! $item) {
                continue;
            }

            $group = $this->stringValue($node['group'] ?? null);

            if ($group === null) {
                $ungroupedItems[] = $item;

                continue;
            }

            $groupedItems[$group][] = $item;
        }

        return [
            'groups' => array_map(
                fn (string $label, array $items): NavigationGroup => NavigationGroup::make($label)->items($items),
                array_keys($groupedItems),
                array_values($groupedItems),
            ),
            'items' => $ungroupedItems,
        ];
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function compileItem(array $node): ?NavigationItem
    {
        $label = $this->stringValue($node['label'] ?? null);

        if ($label === null) {
            return null;
        }

        $item = NavigationItem::make($label);

        if ($icon = $this->stringValue($node['icon'] ?? null)) {
            $item->icon($icon);
        }

        if ($url = $this->stringValue($node['url'] ?? $node['href'] ?? null)) {
            $item->url($url, $this->shouldOpenInNewTab($node));
            $item->isActiveWhen(fn (): bool => $this->matchesCurrentRequest($url));
        }

        if ($sort = $this->integerValue($node['sort'] ?? null)) {
            $item->sort($sort);
        }

        if ($badge = $this->stringValue($node['badge'] ?? null)) {
            $item->badge($badge, $this->stringValue($node['badge_color'] ?? null));
        }

        if ($this->booleanValue($node['is_hidden'] ?? null) === true) {
            $item->hidden();
        }

        $children = array_values(array_filter(array_map(
            fn (mixed $child): ?NavigationItem => is_array($child) ? $this->compileItem($child) : null,
            is_array($node['children'] ?? null) ? $node['children'] : [],
        )));

        if ($children !== []) {
            $item->childItems($children);
        }

        return $item;
    }

    private function matchesCurrentRequest(string $url): bool
    {
        /** @var Request $request */
        $request = request();

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || trim($path) === '') {
            return false;
        }

        $trimmedPath = trim($path, '/');

        if ($trimmedPath === '') {
            return $request->path() === '/';
        }

        return $request->is($trimmedPath) || $request->is($trimmedPath.'/*');
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private function shouldOpenInNewTab(array $node): bool
    {
        return ($node['target'] ?? null) === 'new-tab' || $this->booleanValue($node['open_in_new_tab'] ?? null) === true;
    }

    private function stringValue(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function integerValue(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function booleanValue(mixed $value): ?bool
    {
        return is_bool($value) ? $value : null;
    }
}
