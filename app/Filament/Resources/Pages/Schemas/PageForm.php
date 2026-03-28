<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\PageStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'lg' => 3,
            ])
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->live(onBlur: true)
                    ->required()
                    ->maxLength(255)
                    ->extraInputAttributes(['data-testid' => 'page-title-input'])
                    ->columnSpan(1),
                TextInput::make('slug')
                    ->label('Slug')
                    ->live(onBlur: true)
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->extraInputAttributes(['data-testid' => 'page-slug-input'])
                    ->columnSpan(1),
                Select::make('status')
                    ->label('Status')
                    ->live()
                    ->options(PageStatus::options())
                    ->default(PageStatus::Draft->value)
                    ->required()
                    ->extraInputAttributes(['data-testid' => 'page-status-select'])
                    ->columnSpan(1),
            ]);
    }
}
