<?php

namespace App\Filament\Storybook\Blocks;

use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\StoryRegistry;

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

        return static::$blocks = $blocks;
    }

    public static function findByType(string $type): ?AbstractBlockStory
    {
        return static::all()[$type] ?? null;
    }

    public static function flush(): void
    {
        static::$blocks = null;
    }
}
