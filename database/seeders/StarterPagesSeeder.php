<?php

namespace Database\Seeders;

use App\ComponentSurface;
use App\Models\ComponentDefinition;
use App\Models\Page;
use App\PageStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StarterPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grid = $this->component('filament.layout.grid');
        $section = $this->component('filament.layout.section');
        $textInput = $this->component('filament.form.text_input');
        $select = $this->component('filament.form.select');
        $textColumn = $this->component('filament.table.text_column');
        $badgeColumn = $this->component('filament.table.badge_column');
        $statsOverview = $this->component('filament.widget.stats_overview');
        $chartWidget = $this->component('filament.widget.chart_widget');
        $tableWidget = $this->component('filament.widget.table_widget');

        Page::query()->updateOrCreate(
            ['slug' => 'user-management'],
            [
                'title' => 'User Management',
                'status' => PageStatus::Published,
                'blocks' => [
                    $this->node($section, [
                        'heading' => 'User management workspace',
                        'description' => 'Root section for the user registry schema.',
                    ], [
                        $this->node($grid, [
                            'columns' => 2,
                            'description' => 'Filter controls grid.',
                        ], [
                            $this->node($textInput, [
                                'data_source_type' => 'state',
                                'payload_path' => 'filters.search',
                                'data_source_model' => 'App\\Models\\User',
                                'relationship' => '',
                                'relationship_type' => '',
                                'display_column' => '',
                                'value_column' => 'id',
                                'hydration_logic' => 'state',
                                'label' => 'Search users',
                                'placeholder' => 'Search by name or email',
                                'helper_text' => 'Mapped to the users registry search state.',
                                'is_required' => false,
                                'min_length' => 2,
                                'max_length' => 255,
                                'is_searchable' => true,
                                'input_mode' => 'search',
                            ]),
                            $this->node($select, [
                                'data_source_type' => 'relationship',
                                'payload_path' => 'filters.role',
                                'data_source_model' => 'App\\Models\\User',
                                'relationship' => 'roles',
                                'relationship_type' => 'belongsToMany',
                                'display_column' => 'name',
                                'value_column' => 'id',
                                'hydration_logic' => 'relationship',
                                'label' => 'Role filter',
                                'is_required' => false,
                                'is_searchable' => true,
                                'is_multiple' => true,
                                'validation_rules' => 'nullable|array',
                                'options' => [
                                    ['value' => 'admin', 'label' => 'Admin'],
                                    ['value' => 'editor', 'label' => 'Editor'],
                                ],
                            ]),
                        ]),
                        $this->node($tableWidget, [
                            'data_source_type' => 'model',
                            'payload_path' => 'widgets.user_registry',
                            'data_source_model' => 'App\\Models\\User',
                            'relationship' => '',
                            'relationship_type' => '',
                            'display_column' => '',
                            'value_column' => '',
                            'hydration_logic' => 'table.widget',
                            'widget_key' => 'user_registry',
                            'query_scope' => 'latest()',
                            'pagination_size' => 25,
                        ], [
                            $this->node($textColumn, [
                                'column_path' => 'name',
                                'data_source_model' => 'App\\Models\\User',
                                'relationship' => '',
                                'relationship_type' => '',
                                'hydration_logic' => 'table.column',
                                'label' => 'Name',
                                'is_searchable' => true,
                                'is_sortable' => true,
                            ]),
                            $this->node($textColumn, [
                                'column_path' => 'email',
                                'data_source_model' => 'App\\Models\\User',
                                'relationship' => '',
                                'relationship_type' => '',
                                'hydration_logic' => 'table.column',
                                'label' => 'Email',
                                'is_searchable' => true,
                                'is_sortable' => true,
                            ]),
                            $this->node($badgeColumn, [
                                'column_path' => 'status',
                                'data_source_model' => 'App\\Models\\User',
                                'relationship' => '',
                                'relationship_type' => '',
                                'hydration_logic' => 'table.column',
                                'label' => 'Status',
                                'is_searchable' => true,
                                'is_sortable' => true,
                            ]),
                        ]),
                    ]),
                ],
            ],
        );

        Page::query()->updateOrCreate(
            ['slug' => 'system-analytics'],
            [
                'title' => 'System Analytics',
                'status' => PageStatus::Published,
                'blocks' => [
                    $this->node($grid, [
                        'columns' => 2,
                        'description' => 'Dashboard metrics composition grid.',
                    ], [
                        $this->node($statsOverview, [
                            'data_source_type' => 'aggregate',
                            'payload_path' => 'widgets.system_stats',
                            'data_source_model' => 'App\\Models\\Page',
                            'relationship' => '',
                            'relationship_type' => '',
                            'display_column' => '',
                            'value_column' => '',
                            'hydration_logic' => 'aggregate',
                            'widget_key' => 'system_stats',
                            'query_scope' => 'query()',
                            'stats' => [
                                ['stat_key' => 'users_total', 'label' => 'Users total', 'aggregate' => 'count', 'payload_path' => 'users.total', 'description' => 'Counts App\\Models\\User'],
                                ['stat_key' => 'published_pages', 'label' => 'Published pages', 'aggregate' => 'count', 'payload_path' => 'pages.published', 'description' => 'Counts PageStatus::Published'],
                                ['stat_key' => 'active_definitions', 'label' => 'Active definitions', 'aggregate' => 'count', 'payload_path' => 'definitions.active', 'description' => 'Counts active ComponentDefinition records'],
                            ],
                        ]),
                        $this->node($chartWidget, [
                            'data_source_type' => 'aggregate',
                            'payload_path' => 'widgets.page_status_chart',
                            'data_source_model' => 'App\\Models\\Page',
                            'relationship' => '',
                            'relationship_type' => '',
                            'display_column' => '',
                            'value_column' => '',
                            'hydration_logic' => 'aggregate',
                            'widget_key' => 'page_status_chart',
                            'query_scope' => 'query()',
                            'chart_type' => 'bar',
                            'aggregate' => 'count',
                            'group_by' => 'status',
                            'label' => 'Pages by status',
                            'is_lazy' => true,
                        ]),
                    ]),
                ],
            ],
        );
    }

    private function component(string $handle): ComponentDefinition
    {
        return ComponentDefinition::query()
            ->forSurface(ComponentSurface::Page)
            ->where('handle', $handle)
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array<int, array<string, mixed>>  $children
     * @return array<string, mixed>
     */
    private function node(ComponentDefinition $definition, array $props = [], array $children = []): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => $definition->getBlockType(),
            'surface' => $definition->getSurface()->value,
            'label' => $this->nodeLabel($definition, $props),
            'props' => $definition->normalizeProps(array_merge($definition->getDefaultValues(), $props)),
            'children' => $children,
            'meta' => [
                'slug' => $definition->handle,
                'description' => $definition->description,
                'group' => $definition->category,
                'view' => $definition->view,
                'source' => 'definition',
                'variant' => 'default',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function nodeLabel(ComponentDefinition $definition, array $props): string
    {
        foreach (['label', 'heading', 'widget_key', 'action_name'] as $candidate) {
            $value = $props[$candidate] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return $definition->name;
    }
}
