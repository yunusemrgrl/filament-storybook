<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Storybook\Blocks\BuilderStateMapper;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['blocks'] = app(BuilderStateMapper::class)->fromBuilderState($data['builderBlocks'] ?? []);
        unset($data['builderBlocks']);

        return $data;
    }
}
