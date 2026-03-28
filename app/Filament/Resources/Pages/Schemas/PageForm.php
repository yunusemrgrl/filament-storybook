<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Storybook\Blocks\BlockRegistry;
use App\Filament\Storybook\Support\KnobSchemaCompiler;
use App\PageStatus;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page meta')
                    ->schema([
                        TextInput::make('title')
                            ->live(onBlur: true)
                            ->required()
                            ->maxLength(255)
                            ->extraInputAttributes(['data-testid' => 'page-title-input']),
                        TextInput::make('slug')
                            ->live(onBlur: true)
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->extraInputAttributes(['data-testid' => 'page-slug-input']),
                        Select::make('status')
                            ->live()
                            ->options(PageStatus::options())
                            ->default(PageStatus::Draft->value)
                            ->required()
                            ->extraInputAttributes(['data-testid' => 'page-status-select']),
                    ])
                    ->columns(2),
                Section::make('Page content')
                    ->schema([
                        Builder::make('builderBlocks')
                            ->label('Blocks')
                            ->helperText('Builder katalogu aktif component tanimlari ve sistem blocklarindan otomatik derlenir.')
                            ->blocks(array_map(
                                static fn ($story) => $story->toBuilderBlock(
                                    app(KnobSchemaCompiler::class),
                                ),
                                array_values(BlockRegistry::cms()),
                            ))
                            ->blockIcons()
                            ->addActionLabel('Add block')
                            ->default([])
                            ->extraAttributes(['data-testid' => 'page-builder'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
