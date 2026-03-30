@php
    /** @var \App\Models\ComponentDefinition $componentDefinition */
    /** @var array<string, mixed> $props */

    $propDefinitions = iterator_to_array($componentDefinition->propsCollection());

    $groupedDefinitions = collect($propDefinitions)
        ->groupBy(fn ($definition) => $definition->group);

    $formatValue = static function (mixed $value): string {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : 'N/A';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]';
    };

    $dataSource = [
        'Model' => $props['data_source_model'] ?? null,
        'Payload Path' => $props['payload_path'] ?? $props['column_name'] ?? $props['widget_key'] ?? $props['schema_key'] ?? null,
        'Relationship' => $props['relationship'] ?? null,
        'Hydration Logic' => $props['hydration_logic'] ?? null,
    ];
@endphp

<section
    class="engine-primitive-card"
    data-testid="technical-primitive-{{ str_replace('.', '-', $componentDefinition->handle) }}"
    data-handle="{{ $componentDefinition->handle }}"
>
    <header class="engine-primitive-card__header">
        <div>
            <p class="engine-primitive-card__eyebrow">Filament Primitive</p>
            <h2 class="engine-primitive-card__title">{{ $componentDefinition->name }}</h2>
            @if (filled($componentDefinition->description))
                <p class="engine-primitive-card__description">{{ $componentDefinition->description }}</p>
            @endif
        </div>

        <div class="engine-chip-stack">
            <span class="engine-chip engine-chip--outline">{{ $componentDefinition->getBlockType() }}</span>
            <span class="engine-chip engine-chip--outline">{{ $componentDefinition->handle }}</span>
            <span class="engine-chip">{{ $componentDefinition->getSurface()->label() }}</span>
            <span class="engine-chip engine-chip--muted">{{ $componentDefinition->category }}</span>
        </div>
    </header>

    <div class="engine-runtime-grid">
        <section class="engine-runtime-panel">
            <h3>Data Source</h3>
            <dl class="engine-definition-list">
                @foreach ($dataSource as $label => $value)
                    <div>
                        <dt>{{ $label }}</dt>
                        <dd>{{ filled($value) ? $value : 'N/A' }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="engine-runtime-panel">
            <h3>Definition Metadata</h3>
            <dl class="engine-definition-list">
                <div>
                    <dt>Block Type</dt>
                    <dd>{{ $componentDefinition->getBlockType() }}</dd>
                </div>
                <div>
                    <dt>Runtime View</dt>
                    <dd>{{ $componentDefinition->view }}</dd>
                </div>
                <div>
                    <dt>Schema Fields</dt>
                    <dd>{{ count($propDefinitions) }}</dd>
                </div>
                <div>
                    <dt>Attachable Actions</dt>
                    <dd>{{ is_array($props['actions'] ?? null) ? count($props['actions']) : 0 }}</dd>
                </div>
            </dl>
        </section>
    </div>

    @foreach ($groupedDefinitions as $group => $definitions)
        <section class="engine-runtime-panel">
            <div class="engine-runtime-panel__header">
                <h3>{{ $group }}</h3>
                <span class="engine-chip engine-chip--outline">{{ count($definitions) }} fields</span>
            </div>

            <div class="engine-field-grid">
                @foreach ($definitions as $definition)
                    @php
                        $value = $props[$definition->name] ?? null;
                        $formattedValue = $formatValue($value);
                        $isStructuredValue = is_array($value);
                    @endphp

                    <article class="engine-field-card">
                        <header class="engine-field-card__header">
                            <div>
                                <h4>{{ $definition->label }}</h4>
                                <p>{{ $definition->name }}</p>
                            </div>

                            <div class="engine-chip-stack">
                                <span class="engine-chip engine-chip--outline">{{ $definition->type->value }}</span>
                                @if ($definition->required)
                                    <span class="engine-chip">required</span>
                                @endif
                            </div>
                        </header>

                        @if ($definition->helperText)
                            <p class="engine-field-card__helper">{{ $definition->helperText }}</p>
                        @endif

                        @if ($isStructuredValue)
                            <pre class="engine-code-block">{{ $formattedValue }}</pre>
                        @else
                            <p class="engine-field-card__value">{{ $formattedValue }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endforeach
</section>
