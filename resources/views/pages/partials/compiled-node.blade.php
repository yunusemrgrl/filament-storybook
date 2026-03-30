@php
    /** @var \App\Support\Engine\Compiler\CompiledNode $compiledNode */
    $summary = $compiledNode->summary;
    $summaryItems = collect([
        'Payload path' => $summary['payloadPath'] ?? $summary['statePath'] ?? $summary['columnPath'] ?? $summary['widgetKey'] ?? null,
        'Data source' => $summary['dataSourceModel'] ?? null,
        'Relationship' => $summary['relationship'] ?? null,
        'Hydration logic' => $summary['hydrationLogic'] ?? null,
        'Computed expression' => $summary['computedExpression'] ?? null,
        'Query scope' => $summary['queryScope'] ?? null,
        'Currency' => $summary['currency'] ?? null,
        'Locale' => $summary['locale'] ?? null,
        'Format' => $summary['format'] ?? null,
        'Timezone' => $summary['timezone'] ?? null,
        'Owner model' => $summary['ownerModel'] ?? null,
        'Owner relationship' => $summary['ownerRelationship'] ?? null,
        'Owner relationship type' => $summary['ownerRelationshipType'] ?? null,
        'Heading' => $summary['heading'] ?? null,
        'Event' => $summary['event'] ?? null,
        'Handler' => $summary['handler'] ?? null,
        'Transition from' => $summary['transitionFrom'] ?? null,
        'Transition to' => $summary['transitionTo'] ?? null,
        'Guard' => $summary['guard'] ?? null,
        'Trigger style' => $summary['triggerStyle'] ?? null,
    ])->filter(fn (mixed $value): bool => is_string($value) && trim($value) !== '');
@endphp

<section
    class="engine-runtime-node engine-runtime-node--{{ $compiledNode->family }}"
    data-testid="compiled-node-{{ str($compiledNode->node->slug())->replace('.', '-')->replace('_', '-')->value() }}"
>
    <header class="engine-runtime-node__header">
        <div>
            <p class="engine-runtime-node__eyebrow">{{ str($compiledNode->family)->headline() }}</p>
            <h2 class="engine-runtime-node__title">{{ $compiledNode->node->label }}</h2>
            <p class="engine-runtime-node__slug">{{ $compiledNode->node->slug() }}</p>
        </div>

        <div class="engine-chip-stack">
            @if ($compiledNode->runtimeClass)
                <span class="engine-chip engine-chip--outline">{{ class_basename($compiledNode->runtimeClass) }}</span>
            @endif

            <span class="engine-chip engine-chip--muted">{{ count($compiledNode->children) }} children</span>
        </div>
    </header>

    @if ($summaryItems->isNotEmpty())
        <dl class="engine-runtime-node__facts">
            @foreach ($summaryItems as $label => $value)
                <div class="engine-runtime-node__fact">
                    <dt>{{ $label }}</dt>
                    <dd>{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    @endif

    @if ($compiledNode->children !== [])
        <div class="engine-runtime-node__children">
            @foreach ($compiledNode->children as $childNode)
                @include('pages.partials.compiled-node', ['compiledNode' => $childNode])
            @endforeach
        </div>
    @endif
</section>
