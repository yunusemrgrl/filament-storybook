<?php

namespace App\Filament\Storybook\Blocks;

use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\StoryRegistry;
use App\Models\ComponentDefinition;
use Illuminate\Support\Facades\Schema;

class BlockRegistry
{
    /**
     * @var array<string, AbstractBlockStory>|null
     */
    private static ?array $blocks = null;

    /**
     * @return array<string, AbstractBlockStory>
     */
    public static function all(): array
    {
        if (static::$blocks !== null) {
            return static::$blocks;
        }

        $blocks = [];

        foreach (StoryRegistry::all() as $story) {
            if (! $story instanceof AbstractBlockStory) {
                continue;
            }

            $blocks[$story->getBlockType()] = $story;
        }

        foreach (static::databaseComponents() as $component) {
            $blocks[$component->getBlockType()] = $component;
        }

        return static::$blocks = $blocks;
    }

    public static function findByType(string $type): ?AbstractBlockStory
    {
        return static::all()[$type] ?? null;
    }

    /**
     * @return array<string, AbstractBlockStory>
     */
    public static function cms(): array
    {
        return array_filter(
            static::all(),
            static fn (AbstractBlockStory $story): bool => $story->supportsCmsBuilder(),
        );
    }

    public static function findCmsByType(string $type): ?AbstractBlockStory
    {
        return static::cms()[$type] ?? null;
    }

    public static function flush(): void
    {
        static::$blocks = null;
    }

    /**
     * @return array<int, DatabaseComponentBlock>
     */
    private static function databaseComponents(): array
    {
        if (! app()->bound('db')) {
            return [];
        }

        try {
            if (! Schema::hasTable('component_definitions')) {
                return [];
            }

            return ComponentDefinition::query()
                ->active()
                ->orderBy('name')
                ->get()
                ->map(static fn (ComponentDefinition $definition): DatabaseComponentBlock => $definition->toDatabaseBlock())
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
