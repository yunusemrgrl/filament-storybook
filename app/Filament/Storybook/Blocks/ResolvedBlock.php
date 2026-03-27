<?php

namespace App\Filament\Storybook\Blocks;

use App\Filament\Storybook\AbstractBlockStory;
use App\Filament\Storybook\Blocks\Contracts\BlockDataContract;

readonly class ResolvedBlock
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public AbstractBlockStory $story,
        public BlockDataContract $data,
        public array $payload,
    ) {}

    public function previewView(): string
    {
        return $this->story->getPreviewView();
    }

    public function frontendView(): string
    {
        return $this->story->getFrontendView();
    }

    /**
     * @return array<string, mixed>
     */
    public function previewData(): array
    {
        return array_merge(
            $this->data->toViewData(),
            ['payload' => $this->payload],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function frontendData(): array
    {
        return $this->previewData();
    }
}
