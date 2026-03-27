<?php

namespace App\Filament\Resources\ComponentDefinitions\Pages;

use App\Filament\Resources\ComponentDefinitions\ComponentDefinitionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComponentDefinitions extends ListRecords
{
    protected static string $resource = ComponentDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
