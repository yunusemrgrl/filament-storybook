<?php

namespace App\Filament\Resources\ComponentDefinitions\Pages;

use App\Filament\Resources\ComponentDefinitions\ComponentDefinitionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComponentDefinition extends EditRecord
{
    protected static string $resource = ComponentDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
