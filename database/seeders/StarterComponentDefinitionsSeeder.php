<?php

namespace Database\Seeders;

use App\ComponentPropType;
use App\ComponentSurface;
use App\Models\ComponentDefinition;
use Illuminate\Database\Seeder;

class StarterComponentDefinitionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->definitions() as $definition) {
            ComponentDefinition::query()->updateOrCreate(
                [
                    'handle' => $definition['handle'],
                    'surface' => $definition['surface'],
                ],
                $definition,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function definitions(): array
    {
        return [
            $this->definition(
                name: 'Grid',
                handle: 'filament.layout.grid',
                category: 'Layout',
                description: 'Filament Grid schema container for multi-column layout composition and child node orchestration.',
                props: [
                    $this->numberProp('columns', 'columns', 'Structure', required: true, helperText: 'Maps to Grid::make(columns).'),
                    $this->textProp('description', 'description', 'Appearance', helperText: 'Technical note for this layout container.'),
                ],
                defaults: [
                    'columns' => 2,
                    'description' => 'Multi-column layout container',
                ],
            ),
            $this->definition(
                name: 'Section',
                handle: 'filament.layout.section',
                category: 'Layout',
                description: 'Filament Section schema container for grouped technical controls and nested child nodes.',
                props: [
                    $this->textProp('heading', 'heading', 'Structure', required: true, helperText: 'Maps to Section::make().'),
                    $this->textProp('description', 'description', 'Appearance', helperText: 'Maps to description().'),
                ],
                defaults: [
                    'heading' => 'Technical section',
                    'description' => 'Grouped Filament schema container',
                ],
            ),
            $this->definition(
                name: 'Text Input',
                handle: 'filament.form.text_input',
                category: 'Forms',
                description: 'Filament TextInput primitive for searchable filters, inline form fields, and hydration-bound search state.',
                props: [
                    ...$this->commonDataSourceProps(),
                    $this->textProp('label', 'label', 'Appearance', required: true, helperText: 'Maps to label().'),
                    $this->textProp('placeholder', 'placeholder', 'Appearance', helperText: 'Maps to placeholder().'),
                    $this->textProp('helper_text', 'helperText', 'Appearance', helperText: 'Maps to helperText().'),
                    $this->booleanProp('is_required', 'isRequired', 'Validation', helperText: 'Maps to required().'),
                    $this->textProp('validation_rules', 'validationRules', 'Validation', helperText: 'Pipe-delimited Laravel validation rules.'),
                    $this->numberProp('min_length', 'minLength', 'Validation', helperText: 'Maps to minLength().'),
                    $this->numberProp('max_length', 'maxLength', 'Validation', helperText: 'Maps to maxLength().'),
                    $this->booleanProp('is_searchable', 'isSearchable', 'Validation', helperText: 'Technical search-state toggle for registry filters.'),
                    $this->selectProp('input_mode', 'inputMode', [
                        'text' => 'Text',
                        'email' => 'Email',
                        'search' => 'Search',
                        'numeric' => 'Numeric',
                    ], 'Appearance'),
                    $this->actionsProp(),
                ],
                defaults: [
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
                    'helper_text' => 'Hydrates the table search state.',
                    'is_required' => false,
                    'validation_rules' => 'nullable|string|max:255',
                    'max_length' => 255,
                    'input_mode' => 'search',
                    'actions' => $this->defaultActions('focus_search'),
                ],
            ),
            $this->definition(
                name: 'Select',
                handle: 'filament.form.select',
                category: 'Forms',
                description: 'Filament Select primitive for option maps, relationship filters, and searchable selection state.',
                props: [
                    ...$this->commonDataSourceProps(),
                    $this->textProp('label', 'label', 'Appearance', required: true),
                    $this->booleanProp('is_required', 'isRequired', 'Validation'),
                    $this->booleanProp('is_searchable', 'isSearchable', 'Validation'),
                    $this->booleanProp('is_multiple', 'isMultiple', 'Validation'),
                    $this->textProp('validation_rules', 'validationRules', 'Validation'),
                    $this->repeaterProp('options', 'options', [
                        $this->textProp('value', 'value', 'Structure', required: true),
                        $this->textProp('label', 'label', 'Structure', required: true),
                    ], 'Data Source', helperText: 'Options consumed by options().'),
                    $this->actionsProp(),
                ],
                defaults: [
                    'data_source_type' => 'model',
                    'payload_path' => 'filters.status',
                    'data_source_model' => 'App\\Models\\User',
                    'relationship' => '',
                    'relationship_type' => '',
                    'display_column' => 'name',
                    'value_column' => 'id',
                    'hydration_logic' => 'model',
                    'label' => 'Role filter',
                    'is_required' => false,
                    'is_searchable' => true,
                    'is_multiple' => true,
                    'validation_rules' => 'nullable|array',
                    'options' => [
                        ['value' => 'admin', 'label' => 'Admin'],
                        ['value' => 'editor', 'label' => 'Editor'],
                    ],
                    'actions' => $this->defaultActions('open_role_filter', 'slide_over'),
                ],
            ),
            $this->definition(
                name: 'Money',
                handle: 'filament.form.money',
                category: 'Forms',
                description: 'Financial text input that hydrates integer minor units to human-readable decimal currency values.',
                props: [
                    ...$this->commonDataSourceProps(),
                    $this->textProp('label', 'label', 'Appearance', required: true),
                    $this->textProp('placeholder', 'placeholder', 'Appearance'),
                    $this->textProp('helper_text', 'helperText', 'Appearance'),
                    $this->booleanProp('is_required', 'isRequired', 'Validation'),
                    $this->textProp('validation_rules', 'validationRules', 'Validation'),
                    $this->textProp('currency', 'currency', 'Appearance', helperText: 'ISO currency code for financial formatting.'),
                    $this->textProp('locale', 'locale', 'Appearance', helperText: 'Intl locale used for formatting and parsing.'),
                    $this->numberProp('decimals', 'decimals', 'Appearance', helperText: 'Number of decimal places exposed to editors.'),
                    $this->textProp('prefix', 'prefix', 'Appearance', helperText: 'Optional prefix shown inside the field.'),
                    $this->actionsProp(),
                ],
                defaults: [
                    'data_source_type' => 'state',
                    'payload_path' => 'record.unit_price_cents',
                    'data_source_model' => 'App\\Models\\Product',
                    'relationship' => '',
                    'relationship_type' => '',
                    'display_column' => '',
                    'value_column' => '',
                    'hydration_logic' => 'state',
                    'label' => 'Unit price',
                    'placeholder' => '1250.00',
                    'helper_text' => 'Persists integer cents while presenting a decimal money input.',
                    'is_required' => true,
                    'validation_rules' => 'required|integer|min:0',
                    'currency' => 'USD',
                    'locale' => 'en_US',
                    'decimals' => 2,
                    'prefix' => '$',
                    'actions' => $this->defaultActions('open_pricing_logic'),
                ],
            ),
            $this->definition(
                name: 'Date Time',
                handle: 'filament.form.date_time',
                category: 'Forms',
                description: 'Filament DateTimePicker primitive for schedule-aware business dates and technical workflow timestamps.',
                props: [
                    ...$this->commonDataSourceProps(),
                    $this->textProp('label', 'label', 'Appearance', required: true),
                    $this->textProp('helper_text', 'helperText', 'Appearance'),
                    $this->booleanProp('is_required', 'isRequired', 'Validation'),
                    $this->textProp('validation_rules', 'validationRules', 'Validation'),
                    $this->textProp('format', 'format', 'Appearance', helperText: 'Maps to displayFormat().'),
                    $this->booleanProp('seconds', 'seconds', 'Appearance', helperText: 'Maps to seconds().'),
                    $this->textProp('timezone', 'timezone', 'Appearance', helperText: 'Maps to timezone().'),
                    $this->textProp('min_date', 'minDate', 'Validation', helperText: 'Maps to minDate().'),
                    $this->textProp('max_date', 'maxDate', 'Validation', helperText: 'Maps to maxDate().'),
                    $this->actionsProp(),
                ],
                defaults: [
                    'data_source_type' => 'state',
                    'payload_path' => 'record.issued_at',
                    'data_source_model' => 'App\\Models\\Invoice',
                    'relationship' => '',
                    'relationship_type' => '',
                    'display_column' => '',
                    'value_column' => '',
                    'hydration_logic' => 'state',
                    'label' => 'Issued at',
                    'helper_text' => 'Business issue timestamp for the generated record.',
                    'is_required' => true,
                    'validation_rules' => 'required|date',
                    'format' => 'Y-m-d H:i',
                    'seconds' => false,
                    'timezone' => 'UTC',
                    'min_date' => '',
                    'max_date' => '',
                    'actions' => $this->defaultActions('open_scheduling_logic'),
                ],
            ),
            $this->definition(
                name: 'File Upload',
                handle: 'filament.form.file_upload',
                category: 'Forms',
                description: 'Filament FileUpload primitive for asset ingestion, disk routing, and accepted file constraints.',
                props: [
                    ...$this->commonDataSourceProps(),
                    $this->textProp('label', 'label', 'Appearance', required: true),
                    $this->booleanProp('is_required', 'isRequired', 'Validation'),
                    $this->textProp('validation_rules', 'validationRules', 'Validation'),
                    $this->textProp('disk', 'disk', 'Data Source', helperText: 'Maps to disk().'),
                    $this->textProp('directory', 'directory', 'Data Source', helperText: 'Maps to directory().'),
                    $this->booleanProp('is_multiple', 'isMultiple', 'Validation'),
                    $this->booleanProp('is_image', 'isImage', 'Validation'),
                    $this->textProp('accepted_file_types', 'acceptedFileTypes', 'Validation', helperText: 'Comma-delimited MIME list.'),
                    $this->actionsProp(),
                ],
                defaults: [
                    'data_source_type' => 'state',
                    'payload_path' => 'record.avatar',
                    'data_source_model' => 'App\\Models\\User',
                    'relationship' => '',
                    'relationship_type' => '',
                    'display_column' => '',
                    'value_column' => '',
                    'hydration_logic' => 'state',
                    'label' => 'Avatar asset',
                    'is_required' => false,
                    'validation_rules' => 'nullable|image',
                    'disk' => 'public',
                    'directory' => 'engine/uploads',
                    'is_multiple' => false,
                    'is_image' => true,
                    'accepted_file_types' => 'image/png,image/jpeg',
                    'actions' => $this->defaultActions('replace_asset'),
                ],
            ),
            $this->definition(
                name: 'Repeater',
                handle: 'filament.form.repeater',
                category: 'Forms',
                description: 'Filament Repeater primitive for structured nested arrays, relationship collections, and item-level schema definitions.',
                props: [
                    ...$this->commonDataSourceProps(),
                    $this->textProp('label', 'label', 'Appearance', required: true),
                    $this->textProp('item_label_path', 'itemLabelPath', 'Structure', helperText: 'Used for itemLabel().'),
                    $this->textProp('validation_rules', 'validationRules', 'Validation'),
                    $this->actionsProp(),
                ],
                defaults: [
                    'data_source_type' => 'state',
                    'payload_path' => 'form.items',
                    'data_source_model' => 'App\\Models\\Page',
                    'relationship' => '',
                    'relationship_type' => '',
                    'display_column' => '',
                    'value_column' => '',
                    'hydration_logic' => 'state',
                    'label' => 'Relationship collection',
                    'item_label_path' => 'label',
                    'validation_rules' => 'nullable|array',
                    'actions' => $this->defaultActions('append_schema_field'),
                ],
            ),
            $this->definition(
                name: 'Action Button',
                handle: 'filament.action.button',
                category: 'Actions',
                description: 'Filament action pipeline primitive for workflow transitions, handlers, and modal or slide-over execution metadata.',
                props: [
                    $this->textProp('action_name', 'actionName', 'Actions', required: true, helperText: 'Technical action identifier.'),
                    $this->textProp('label', 'label', 'Appearance', required: true, helperText: 'Maps to action label.'),
                    $this->selectProp('trigger_style', 'triggerStyle', [
                        'button' => 'Button',
                        'modal' => 'Modal',
                        'slide_over' => 'Slide-over',
                    ], 'Appearance', required: true),
                    $this->selectProp('color', 'color', [
                        'primary' => 'Primary',
                        'secondary' => 'Secondary',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'danger' => 'Danger',
                    ], 'Appearance'),
                    $this->textProp('handler', 'handler', 'Actions', helperText: 'Laravel action or service class executed by the runtime.'),
                    $this->textProp('event', 'event', 'Actions', helperText: 'Logical event name emitted by the workflow.'),
                    $this->textProp('transition_from', 'transitionFrom', 'Actions', helperText: 'Allowed source state.'),
                    $this->textProp('transition_to', 'transitionTo', 'Actions', helperText: 'Target state after successful execution.'),
                    $this->textProp('guard', 'guard', 'Actions', helperText: 'Optional policy/guard expression.'),
                    $this->booleanProp('requires_confirmation', 'requiresConfirmation', 'Actions'),
                ],
                defaults: [
                    'action_name' => 'execute_transition',
                    'label' => 'Execute action',
                    'trigger_style' => 'button',
                    'color' => 'primary',
                    'handler' => 'App\\StarterKits\\StrukturaEngine\\Actions\\ExecuteTransition',
                    'event' => 'schema.transition',
                    'transition_from' => 'draft',
                    'transition_to' => 'published',
                    'guard' => 'canExecuteTransition',
                    'requires_confirmation' => true,
                ],
            ),
            $this->definition(
                name: 'Text Column',
                handle: 'filament.table.text_column',
                category: 'Tables',
                description: 'Filament TextColumn primitive for textual table output, searchable data, and sortable table state.',
                props: [
                    ...$this->commonColumnDataSourceProps(),
                    $this->textProp('label', 'label', 'Appearance', required: true),
                    $this->booleanProp('is_searchable', 'isSearchable', 'Validation'),
                    $this->booleanProp('is_sortable', 'isSortable', 'Validation'),
                    $this->booleanProp('is_toggleable', 'isToggleable', 'Appearance'),
                    $this->numberProp('limit', 'limit', 'Appearance'),
                    $this->actionsProp(),
                ],
                defaults: [
                    'data_source_type' => 'model',
                    'column_path' => 'name',
                    'data_source_model' => 'App\\Models\\User',
                    'relationship' => '',
                    'relationship_type' => '',
                    'display_column' => 'name',
                    'value_column' => 'id',
                    'hydration_logic' => 'table.column',
                    'label' => 'Name',
                    'is_searchable' => true,
                    'is_sortable' => true,
                    'is_toggleable' => false,
                    'limit' => 40,
                    'actions' => $this->defaultActions('open_record'),
                ],
            ),
            $this->definition(
                name: 'Image Column',
                handle: 'filament.table.image_column',
                category: 'Tables',
                description: 'Filament ImageColumn primitive for disk-backed media columns, shape, and sizing rules.',
                props: [
                    ...$this->commonColumnDataSourceProps(),
                    $this->textProp('label', 'label', 'Appearance', required: true),
                    $this->textProp('disk', 'disk', 'Data Source'),
                    $this->booleanProp('is_circular', 'isCircular', 'Appearance'),
                    $this->numberProp('image_height', 'imageHeight', 'Appearance'),
                    $this->actionsProp(),
                ],
                defaults: [
                    'data_source_type' => 'model',
                    'column_path' => 'avatar',
                    'data_source_model' => 'App\\Models\\User',
                    'relationship' => '',
                    'relationship_type' => '',
                    'display_column' => 'name',
                    'value_column' => 'id',
                    'hydration_logic' => 'table.column',
                    'label' => 'Avatar',
                    'disk' => 'public',
                    'is_circular' => true,
                    'image_height' => 40,
                    'actions' => $this->defaultActions('inspect_asset', 'slide_over'),
                ],
            ),
            $this->definition(
                name: 'Badge Column',
                handle: 'filament.table.badge_column',
                category: 'Tables',
                description: 'Filament BadgeColumn primitive for state-to-color mapping and status display logic.',
                props: [
                    ...$this->commonColumnDataSourceProps(),
                    $this->textProp('label', 'label', 'Appearance', required: true),
                    $this->booleanProp('is_searchable', 'isSearchable', 'Validation'),
                    $this->booleanProp('is_sortable', 'isSortable', 'Validation'),
                    $this->repeaterProp('state_colors', 'stateColors', [
                        $this->textProp('state', 'state', 'Structure', required: true),
                        $this->selectProp('color', 'color', [
                            'gray' => 'Gray',
                            'success' => 'Success',
                            'warning' => 'Warning',
                            'danger' => 'Danger',
                        ], 'Structure', required: true),
                    ], 'Appearance'),
                    $this->actionsProp(),
                ],
                defaults: [
                    'data_source_type' => 'model',
                    'column_path' => 'status',
                    'data_source_model' => 'App\\Models\\User',
                    'relationship' => '',
                    'relationship_type' => '',
                    'display_column' => 'name',
                    'value_column' => 'id',
                    'hydration_logic' => 'table.column',
                    'label' => 'Status',
                    'is_searchable' => true,
                    'is_sortable' => true,
                    'state_colors' => [
                        ['state' => 'draft', 'color' => 'warning'],
                        ['state' => 'published', 'color' => 'success'],
                    ],
                    'actions' => $this->defaultActions('open_status_logic', 'modal'),
                ],
            ),
            $this->definition(
                name: 'Icon Column',
                handle: 'filament.table.icon_column',
                category: 'Tables',
                description: 'Filament IconColumn primitive for boolean state mapping, true/false icons, and technical status glyphs.',
                props: [
                    ...$this->commonColumnDataSourceProps(),
                    $this->textProp('label', 'label', 'Appearance', required: true),
                    $this->booleanProp('is_boolean', 'isBoolean', 'Validation'),
                    $this->textProp('true_icon', 'trueIcon', 'Appearance'),
                    $this->textProp('false_icon', 'falseIcon', 'Appearance'),
                    $this->selectProp('color', 'color', [
                        'gray' => 'Gray',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'danger' => 'Danger',
                    ], 'Appearance'),
                    $this->actionsProp(),
                ],
                defaults: [
                    'data_source_type' => 'model',
                    'column_path' => 'is_active',
                    'data_source_model' => 'App\\Models\\User',
                    'relationship' => '',
                    'relationship_type' => '',
                    'display_column' => 'name',
                    'value_column' => 'id',
                    'hydration_logic' => 'table.column',
                    'label' => 'Active',
                    'is_boolean' => true,
                    'true_icon' => 'heroicon-o-check-circle',
                    'false_icon' => 'heroicon-o-x-circle',
                    'color' => 'success',
                    'actions' => $this->defaultActions('inspect_boolean_state'),
                ],
            ),
            $this->definition(
                name: 'Stats Overview',
                handle: 'filament.widget.stats_overview',
                category: 'Widgets',
                description: 'Filament StatsOverview widget primitive for aggregate metric cards and KPI hydration.',
                props: [
                    ...$this->commonDataSourceProps(),
                    $this->textProp('widget_key', 'widgetKey', 'Data Source', required: true),
                    $this->textProp('query_scope', 'queryScope', 'Data Source'),
                    $this->repeaterProp('stats', 'stats', [
                        $this->textProp('stat_key', 'statKey', 'Structure', required: true),
                        $this->textProp('label', 'label', 'Structure', required: true),
                        $this->selectProp('aggregate', 'aggregate', [
                            'count' => 'count()',
                            'sum' => 'sum()',
                            'avg' => 'avg()',
                        ], 'Structure', required: true),
                        $this->textProp('payload_path', 'payloadPath', 'Structure'),
                        $this->textProp('description', 'description', 'Structure'),
                    ], 'Data Source'),
                    $this->actionsProp(),
                ],
                defaults: [
                    'data_source_type' => 'aggregate',
                    'payload_path' => 'widgets.system_stats',
                    'data_source_model' => 'App\\Models\\Page',
                    'relationship' => '',
                    'relationship_type' => '',
                    'display_column' => '',
                    'value_column' => '',
                    'hydration_logic' => 'aggregate',
                    'widget_key' => 'system_stats',
                    'query_scope' => 'published()',
                    'stats' => [
                        ['stat_key' => 'users_total', 'label' => 'Users total', 'aggregate' => 'count', 'payload_path' => 'users.total', 'description' => 'Counts App\\Models\\User'],
                        ['stat_key' => 'pages_published', 'label' => 'Published pages', 'aggregate' => 'count', 'payload_path' => 'pages.published', 'description' => 'Counts published pages'],
                    ],
                    'actions' => $this->defaultActions('refresh_stats'),
                ],
            ),
            $this->definition(
                name: 'Chart Widget',
                handle: 'filament.widget.chart_widget',
                category: 'Widgets',
                description: 'Filament ChartWidget primitive for grouped aggregates, chart series, and lazy-loaded analytics.',
                props: [
                    ...$this->commonDataSourceProps(),
                    $this->textProp('widget_key', 'widgetKey', 'Data Source', required: true),
                    $this->textProp('query_scope', 'queryScope', 'Data Source'),
                    $this->selectProp('chart_type', 'chartType', [
                        'line' => 'Line',
                        'bar' => 'Bar',
                        'pie' => 'Pie',
                    ], 'Appearance'),
                    $this->selectProp('aggregate', 'aggregate', [
                        'count' => 'count()',
                        'sum' => 'sum()',
                        'avg' => 'avg()',
                    ], 'Data Source'),
                    $this->textProp('group_by', 'groupBy', 'Data Source'),
                    $this->textProp('label', 'label', 'Appearance'),
                    $this->booleanProp('is_lazy', 'isLazy', 'Appearance'),
                    $this->actionsProp(),
                ],
                defaults: [
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
                    'actions' => $this->defaultActions('refresh_chart'),
                ],
            ),
            $this->definition(
                name: 'Table Widget',
                handle: 'filament.widget.table_widget',
                category: 'Widgets',
                description: 'Filament TableWidget primitive for Eloquent-backed table registry output, columns, and query scopes.',
                props: [
                    ...$this->commonDataSourceProps(),
                    $this->textProp('widget_key', 'widgetKey', 'Data Source', required: true),
                    $this->textProp('query_scope', 'queryScope', 'Data Source'),
                    $this->numberProp('pagination_size', 'paginationSize', 'Appearance'),
                    $this->actionsProp(),
                ],
                defaults: [
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
                    'actions' => $this->defaultActions('open_row_actions', 'slide_over'),
                ],
            ),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $props
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    private function definition(
        string $name,
        string $handle,
        string $category,
        string $description,
        array $props,
        array $defaults,
    ): array {
        return [
            'name' => $name,
            'handle' => $handle,
            'surface' => ComponentSurface::Page,
            'category' => $category,
            'view' => 'page-builder.components.filament-primitive',
            'description' => $description,
            'props' => $props,
            'default_values' => $defaults,
            'is_active' => true,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function commonDataSourceProps(): array
    {
        return [
            $this->selectProp('data_source_type', 'dataSourceType', [
                'state' => 'State',
                'model' => 'Model',
                'relationship' => 'Relationship',
                'aggregate' => 'Aggregate',
            ], 'Data Source'),
            $this->textProp('payload_path', 'payloadPath', 'Data Source', required: true, helperText: 'Logical JSON path or state key.'),
            $this->textProp('data_source_model', 'dataSourceModel', 'Data Source', helperText: 'Eloquent model class.'),
            $this->textProp('relationship', 'relationship', 'Data Source', helperText: 'Maps to relationship() when relevant.'),
            $this->textProp('relationship_type', 'relationshipType', 'Data Source', helperText: 'Technical relationship category for compiler validation.'),
            $this->textProp('display_column', 'displayColumn', 'Data Source', helperText: 'Presentation column for relationship-backed options.'),
            $this->textProp('value_column', 'valueColumn', 'Data Source', helperText: 'Persisted value column for relationship-backed options.'),
            $this->selectProp('hydration_logic', 'hydrationLogic', [
                'state' => 'State hydration',
                'relationship' => 'Relationship hydration',
                'aggregate' => 'Aggregate hydration',
                'computed' => 'Computed hydration',
                'table.widget' => 'Table widget hydration',
                'table.column' => 'Table column hydration',
            ], 'Data Source'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function commonColumnDataSourceProps(): array
    {
        return [
            $this->selectProp('data_source_type', 'dataSourceType', [
                'model' => 'Model',
                'relationship' => 'Relationship',
            ], 'Data Source'),
            $this->textProp('column_path', 'columnPath', 'Data Source', required: true, helperText: 'Maps to make(columnPath).'),
            $this->textProp('data_source_model', 'dataSourceModel', 'Data Source', helperText: 'Eloquent model class.'),
            $this->textProp('relationship', 'relationship', 'Data Source', helperText: 'Relationship or nested relation path.'),
            $this->textProp('relationship_type', 'relationshipType', 'Data Source', helperText: 'Technical relationship category for compiler validation.'),
            $this->textProp('display_column', 'displayColumn', 'Data Source', helperText: 'Presentation column for relationship-backed options.'),
            $this->textProp('value_column', 'valueColumn', 'Data Source', helperText: 'Persisted value column for relationship-backed options.'),
            $this->textProp('hydration_logic', 'hydrationLogic', 'Data Source', helperText: 'Technical table hydration identifier.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function textProp(
        string $name,
        string $label,
        string $group,
        bool $required = false,
        ?string $helperText = null,
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'type' => ComponentPropType::Text->value,
            'group' => $group,
            'required' => $required,
            'helper_text' => $helperText,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function numberProp(
        string $name,
        string $label,
        string $group,
        bool $required = false,
        ?string $helperText = null,
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'type' => ComponentPropType::Number->value,
            'group' => $group,
            'required' => $required,
            'helper_text' => $helperText,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function booleanProp(
        string $name,
        string $label,
        string $group,
        bool $required = false,
        ?string $helperText = null,
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'type' => ComponentPropType::Boolean->value,
            'group' => $group,
            'required' => $required,
            'helper_text' => $helperText,
        ];
    }

    /**
     * @param  array<string, string>  $options
     * @return array<string, mixed>
     */
    private function selectProp(
        string $name,
        string $label,
        array $options,
        string $group,
        bool $required = false,
        ?string $helperText = null,
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'type' => ComponentPropType::Select->value,
            'group' => $group,
            'required' => $required,
            'helper_text' => $helperText,
            'options' => collect($options)
                ->map(fn (string $optionLabel, string $value): array => [
                    'value' => $value,
                    'label' => $optionLabel,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private function repeaterProp(
        string $name,
        string $label,
        array $fields,
        string $group,
        bool $required = false,
        ?string $helperText = null,
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'type' => ComponentPropType::Repeater->value,
            'group' => $group,
            'required' => $required,
            'helper_text' => $helperText,
            'fields' => $fields,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function actionsProp(): array
    {
        return $this->repeaterProp('actions', 'actions', [
            $this->textProp('action_name', 'actionName', 'Structure', required: true),
            $this->textProp('label', 'label', 'Structure', required: true),
            $this->selectProp('trigger_style', 'triggerStyle', [
                'button' => 'Button',
                'modal' => 'Modal',
                'slide_over' => 'Slide-over',
            ], 'Structure', required: true),
            $this->selectProp('color', 'color', [
                'primary' => 'Primary',
                'secondary' => 'Secondary',
                'success' => 'Success',
                'warning' => 'Warning',
                'danger' => 'Danger',
            ], 'Structure'),
            $this->textProp('handler', 'handler', 'Structure'),
            $this->textProp('event', 'event', 'Structure'),
            $this->textProp('transition_from', 'transitionFrom', 'Structure'),
            $this->textProp('transition_to', 'transitionTo', 'Structure'),
            $this->textProp('guard', 'guard', 'Structure'),
            $this->booleanProp('requires_confirmation', 'requiresConfirmation', 'Structure'),
        ], 'Actions', helperText: 'Attachable Filament action metadata.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultActions(string $actionName, string $triggerStyle = 'button'): array
    {
        return [[
            'action_name' => $actionName,
            'label' => 'Open action',
            'trigger_style' => $triggerStyle,
            'color' => 'primary',
            'handler' => 'App\\StarterKits\\StrukturaEngine\\Actions\\OpenAction',
            'event' => $actionName,
            'transition_from' => '',
            'transition_to' => '',
            'guard' => '',
            'requires_confirmation' => false,
        ]];
    }
}
