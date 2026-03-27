<?php

namespace App\Filament\Storybook;

use App\Filament\Storybook\Blocks\Contracts\BlockDataContract;

abstract class AbstractBlockStory extends AbstractKnobStory
{
    public function getRenderType(): string
    {
        return 'block';
    }

    abstract public function getBlockType(): string;

    public function getBlockVersion(): int
    {
        return 1;
    }

    /**
     * @param  array<string, mixed>  $knobs
     * @return array<string, mixed>
     */
    abstract public function makeBlockPayload(array $knobs, string $preset): array;

    /**
     * @param  array<string, mixed>  $payload
     */
    abstract public function resolveBlockData(array $payload): BlockDataContract;

    abstract public function getFrontendView(): string;

    public function getPreviewView(): string
    {
        return $this->getFrontendView();
    }

    /**
     * @return array<string, mixed>
     */
    public function getPresetPayload(string $preset): array
    {
        return $this->makeBlockPayload(
            $this->getPresetValues($preset),
            $preset,
        );
    }
}
