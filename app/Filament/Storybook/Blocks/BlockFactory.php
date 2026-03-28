<?php

namespace App\Filament\Storybook\Blocks;

use App\ComponentSurface;
use InvalidArgumentException;

class BlockFactory
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function make(array $payload): ResolvedBlock
    {
        $type = $payload['type'] ?? null;

        if (! is_string($type) || $type === '') {
            throw new InvalidArgumentException('Block payload icin type zorunludur.');
        }

        $story = BlockRegistry::findByTypeForSurface(ComponentSurface::Page, $type);

        if (! $story) {
            throw new InvalidArgumentException("Kayitli block bulunamadi: {$type}");
        }

        return new ResolvedBlock(
            story: $story,
            data: $story->resolveBlockData($payload),
            payload: $payload,
        );
    }
}
