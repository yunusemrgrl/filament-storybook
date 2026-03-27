<?php

namespace App\Filament\Resources\ComponentDefinitions\Schemas;

use App\ComponentPropDefinitionCollection;
use App\ComponentPropType;
use App\Filament\Storybook\Support\KnobSchemaCompiler;
use App\Models\ComponentDefinition;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ComponentDefinitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Component info')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, Get $get): void {
                                $currentHandle = trim((string) $get('handle'));

                                if ($currentHandle !== '') {
                                    return;
                                }

                                $set('handle', Str::snake((string) $state));
                            })
                            ->extraInputAttributes(['data-testid' => 'component-name-input']),
                        TextInput::make('handle')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Builder ve runtime tarafinda benzersiz block tipi olarak kullanilir.')
                            ->extraInputAttributes(['data-testid' => 'component-handle-input']),
                        Select::make('view')
                            ->label('Template view')
                            ->options(ComponentDefinition::viewOptions())
                            ->required()
                            ->searchable()
                            ->helperText('Page builder componenti hangi Blade template ile render edecek?')
                            ->extraInputAttributes(['data-testid' => 'component-view-select']),
                        TextInput::make('category')
                            ->maxLength(255)
                            ->default('General')
                            ->helperText('Editor katalogunda gruplama icin kullanilir.'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Kapaliysa page builder katalogunda gorunmez.'),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Merchant veya content editor icin kisa aciklama.'),
                    ])
                    ->columns(2),
                Section::make('Props')
                    ->description('ikas benzeri olarak componentin prop contracti burada tanimlanir.')
                    ->schema([
                        Repeater::make('props')
                            ->label('Prop definitions')
                            ->schema(self::propDefinitionSchema())
                            ->columns(1)
                            ->default([])
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->addActionLabel('Add prop')
                            ->itemLabel(static function (array $state): ?string {
                                $label = trim((string) ($state['label'] ?? '') ?: (string) ($state['name'] ?? ''));

                                return $label !== '' ? $label : null;
                            })
                            ->live()
                            ->extraAttributes(['data-testid' => 'component-props-repeater'])
                            ->columnSpanFull(),
                    ]),
                Section::make('Default values')
                    ->description('Ayni prop grammari ikinci kez kullanilarak ilk page instance statei uretilir.')
                    ->schema(fn (Get $get): array => self::defaultValueSchema($get))
                    ->visible(fn (Get $get): bool => filled($get('props')))
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    private static function propDefinitionSchema(bool $nested = false): array
    {
        $schema = [
            TextInput::make('name')
                ->required()
                ->live(onBlur: true)
                ->helperText('Machine name, payload key olarak saklanir.')
                ->afterStateUpdated(function (Set $set, ?string $state, Get $get): void {
                    $currentLabel = trim((string) $get('label'));

                    if ($currentLabel === '' && is_string($state)) {
                        $set('label', Str::headline($state));
                    }

                    if (is_string($state)) {
                        $set('name', Str::snake($state));
                    }
                }),
            TextInput::make('label')
                ->required()
                ->helperText('Editor tarafinda field label olarak gorunur.'),
            Select::make('type')
                ->required()
                ->options($nested ? ComponentPropType::nestedOptions() : ComponentPropType::options())
                ->default(ComponentPropType::Text->value)
                ->live(),
            TextInput::make('group')
                ->default('Content')
                ->helperText('Fieldlari editor icinde mantiksal sectionlara ayirir.'),
            TextInput::make('helper_text')
                ->label('Helper text')
                ->helperText('Merchant icin field altindaki kisa yonlendirme.'),
            Toggle::make('required')
                ->default(false),
            Repeater::make('options')
                ->label('Select options')
                ->schema([
                    TextInput::make('value')
                        ->required(),
                    TextInput::make('label')
                        ->required(),
                ])
                ->addActionLabel('Add option')
                ->columns(2)
                ->default([])
                ->hidden(fn (Get $get): bool => $get('type') !== ComponentPropType::Select->value)
                ->columnSpanFull(),
        ];

        if (! $nested) {
            $schema[] = TextInput::make('disk')
                ->default('public')
                ->hidden(fn (Get $get): bool => $get('type') !== ComponentPropType::File->value);

            $schema[] = TextInput::make('directory')
                ->default('page-builder/uploads')
                ->hidden(fn (Get $get): bool => $get('type') !== ComponentPropType::File->value);

            $schema[] = Toggle::make('image')
                ->default(false)
                ->hidden(fn (Get $get): bool => $get('type') !== ComponentPropType::File->value);

            $schema[] = Repeater::make('fields')
                ->label('Repeater fields')
                ->schema(self::propDefinitionSchema(nested: true))
                ->default([])
                ->collapsible()
                ->cloneable()
                ->addActionLabel('Add repeater field')
                ->itemLabel(static function (array $state): ?string {
                    $label = trim((string) ($state['label'] ?? '') ?: (string) ($state['name'] ?? ''));

                    return $label !== '' ? $label : null;
                })
                ->hidden(fn (Get $get): bool => $get('type') !== ComponentPropType::Repeater->value)
                ->columnSpanFull();
        }

        return $schema;
    }

    /**
     * @return array<int, mixed>
     */
    private static function defaultValueSchema(Get $get): array
    {
        $definitions = ComponentPropDefinitionCollection::fromArray($get('props') ?? []);

        if ($definitions->count() === 0) {
            return [];
        }

        return [
            Group::make()
                ->schema(app(KnobSchemaCompiler::class)->compile(
                    $definitions->toKnobDefinitions(),
                    testIdPrefix: 'component-default',
                ))
                ->statePath('default_values'),
        ];
    }
}
