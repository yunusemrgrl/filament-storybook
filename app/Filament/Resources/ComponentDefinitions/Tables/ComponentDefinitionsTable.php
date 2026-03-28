<?php

namespace App\Filament\Resources\ComponentDefinitions\Tables;

use App\ComponentSurface;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComponentDefinitionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('handle')
                    ->searchable(),
                TextColumn::make('surface')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => ($state instanceof ComponentSurface
                        ? $state
                        : ComponentSurface::tryFrom((string) $state) ?? ComponentSurface::Page)
                        ->label())
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                TextColumn::make('view')
                    ->label('Template')
                    ->toggleable(),
                TextColumn::make('props_count')
                    ->label('Props')
                    ->state(fn ($record): int => $record->propsCollection()->count()),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('updated_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
