<?php

namespace App\Filament\Storybook\Blocks;

use App\ComponentSurface;
use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\StoryRegistry;
use App\Models\ComponentDefinition;
use Illuminate\Support\Facades\Schema;

class BlockRegistry
{
    /**
     * @var array<string, array<string, AbstractBlockStory>>
     */
    private static array $codeBlocks = [];

    /**
     * @return array<string, AbstractBlockStory>
     */
    public static function all(): array
    {
        return static::forSurface(ComponentSurface::Page);
    }

    /**
     * @return array<string, AbstractBlockStory>
     */
    public static function forSurface(ComponentSurface|string $surface): array
    {
        $surface = static::normalizeSurface($surface);
        $blocks = static::codeBlocksForSurface($surface);

        foreach (static::databaseComponentsForSurface($surface) as $component) {
            $blocks[$component->getBlockType()] = $component;
        }

        return $blocks;
    }

    public static function findByType(string $type): ?AbstractBlockStory
    {
        return static::findByTypeForSurface(ComponentSurface::Page, $type);
    }

    public static function findByTypeForSurface(ComponentSurface|string $surface, string $type): ?AbstractBlockStory
    {
        return static::forSurface($surface)[$type] ?? null;
    }

    /**
     * @return array<string, AbstractBlockStory>
     */
    public static function cms(): array
    {
        return static::cmsForSurface(ComponentSurface::Page);
    }

    /**
     * @return array<string, AbstractBlockStory>
     */
    public static function cmsForSurface(ComponentSurface|string $surface): array
    {
        return array_filter(
            static::forSurface($surface),
            static fn (AbstractBlockStory $story): bool => $story->supportsCmsBuilder(),
        );
    }

    public static function findCmsByType(string $type): ?AbstractBlockStory
    {
        return static::findCmsByTypeForSurface(ComponentSurface::Page, $type);
    }

    public static function findCmsByTypeForSurface(ComponentSurface|string $surface, string $type): ?AbstractBlockStory
    {
        return static::cmsForSurface($surface)[$type] ?? null;
    }

    public static function flush(): void
    {
        static::$codeBlocks = [];
    }

    /**
     * @return array<int, DatabaseComponentBlock>
     */
    public static function databaseComponentsForSurface(ComponentSurface|string $surface): array
    {
        $surface = static::normalizeSurface($surface);

        if (! app()->bound('db')) {
            return [];
        }

        try {
            if (! Schema::hasTable('component_definitions')) {
                return [];
            }

            return ComponentDefinition::query()
                ->active()
                ->forSurface($surface)
                ->orderBy('name')
                ->get()
                ->map(static fn (ComponentDefinition $definition): DatabaseComponentBlock => $definition->toDatabaseBlock())
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private static function normalizeSurface(ComponentSurface|string $surface): ComponentSurface
    {
        return $surface instanceof ComponentSurface
            ? $surface
            : ComponentSurface::tryFrom($surface) ?? ComponentSurface::Page;
    }

    /**
     * @return array<string, AbstractBlockStory>
     */
    private static function codeBlocksForSurface(ComponentSurface $surface): array
    {
        $cacheKey = $surface->value;

        if (array_key_exists($cacheKey, static::$codeBlocks)) {
            return static::$codeBlocks[$cacheKey];
        }

        $blocks = [];

        foreach (StoryRegistry::codeStories() as $story) {
            if (! $story instanceof AbstractBlockStory || $story->getSurface() !== $surface) {
                continue;
            }

            $blocks[$story->getBlockType()] = $story;
        }

        return static::$codeBlocks[$cacheKey] = $blocks;
    }
}
