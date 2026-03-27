<?php

namespace App\Filament\Resources\ComponentDefinitions;

use App\Filament\Resources\ComponentDefinitions\Pages\CreateComponentDefinition;
use App\Filament\Resources\ComponentDefinitions\Pages\EditComponentDefinition;
use App\Filament\Resources\ComponentDefinitions\Pages\ListComponentDefinitions;
use App\Filament\Resources\ComponentDefinitions\Schemas\ComponentDefinitionForm;
use App\Filament\Resources\ComponentDefinitions\Tables\ComponentDefinitionsTable;
use App\Models\ComponentDefinition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ComponentDefinitionResource extends Resource
{
    protected static ?string $model = ComponentDefinition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Builder';

    public static function form(Schema $schema): Schema
    {
        return ComponentDefinitionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComponentDefinitionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComponentDefinitions::route('/'),
            'create' => CreateComponentDefinition::route('/create'),
            'edit' => EditComponentDefinition::route('/{record}/edit'),
        ];
    }
}
