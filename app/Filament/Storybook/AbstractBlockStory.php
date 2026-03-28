<?php

namespace App\Filament\Storybook;

use App\ComponentSurface;
use App\Filament\Storybook\Blocks\Contracts\BlockDataContract;
use App\Filament\Storybook\Support\KnobSchemaCompiler;
use Filament\Forms\Components\Builder\Block;

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

    public function getSurface(): ComponentSurface
    {
        return ComponentSurface::Page;
    }

    public function supportsCmsBuilder(): bool
    {
        return $this->getSurface() === ComponentSurface::Page;
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

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function makeBuilderData(array $payload): array
    {
        $variant = is_string($payload['variant'] ?? null) ? $payload['variant'] : 'default';

        return $this->getPresetValues($variant);
    }

    public function getBuilderItemLabel(?array $state = null): string
    {
        return $this->title;
    }

    public function getBuilderPreviewView(): ?string
    {
        return null;
    }

    public function toBuilderBlock(KnobSchemaCompiler $compiler): Block
    {
        $block = Block::make($this->getBlockType())
            ->label(fn (?array $state): string => $this->getBuilderItemLabel($state))
            ->icon($this->icon)
            ->schema($compiler->compile($this->knobs(), live: true, testIdPrefix: 'builder-field'))
            ->columns(1);

        if ($previewView = $this->getBuilderPreviewView()) {
            $block->preview($previewView);
        }

        return $block;
    }

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
