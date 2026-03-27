@php
    $booleanType = \App\Filament\Storybook\KnobDefinition::TYPE_BOOLEAN;
    $numberType = \App\Filament\Storybook\KnobDefinition::TYPE_NUMBER;
    $selectType = \App\Filament\Storybook\KnobDefinition::TYPE_SELECT;
@endphp

<section class="docs-card playground-controls-panel">
    <div class="docs-card-head">
        <div>
            <p class="docs-kicker">Controls</p>
            <h2 class="docs-section-title">Knobs</h2>
        </div>

        <p class="docs-card-copy">
            Knobs yalnizca secili variant ile uyumlu ayarlari acarak anlamsiz kombinasyonlari gizler.
        </p>
    </div>

    <div class="playground-explainer">
        <div class="playground-explainer-item">
            <span class="playground-explainer-label">Variant</span>
            <span>Dokumanda referans olarak gosterdigimiz kurgu.</span>
        </div>

        <div class="playground-explainer-item">
            <span class="playground-explainer-label">Knobs</span>
            <span>Bu kurgunun ustune eklenen gecici playground ayarlari.</span>
        </div>

        <div class="playground-explainer-item">
            <span class="playground-explainer-label">Levels</span>
            <span>Prototype primitive davranisi, Component recipe katmanini, Page ise block seviyesini anlatir.</span>
        </div>
    </div>

    @foreach ($groupedKnobDefinitions as $groupLabel => $knobs)
        <section class="knob-section" wire:key="knob-group-{{ $groupLabel }}">
            <div class="knob-section-head">
                <h3 class="knob-section-title">{{ $groupLabel }}</h3>
            </div>

            <div class="knobs-grid">
                @foreach ($knobs as $knob)
                    @php
                        $name = $knob->getName();
                        $value = $knobValues[$name] ?? $knob->getDefault();
                        $isBoolean = $knob->getType() === $booleanType;
                        $isNumber = $knob->getType() === $numberType;
                        $isSelect = $knob->getType() === $selectType;
                    @endphp

                    <div class="knob-field" wire:key="knob-{{ $name }}" dusk="knob-{{ $name }}">
                        <div class="knob-label">
                            <span>{{ $knob->getLabel() }}</span>

                            <span class="knob-level-badge">{{ $knob->getLevelLabel() }}</span>

                            @if (filled($knob->getHelperText()))
                                <x-filament::icon-button
                                    class="knob-help-btn"
                                    color="gray"
                                    icon="heroicon-o-question-mark-circle"
                                    icon-size="sm"
                                    :label="$knob->getHelperText()"
                                    size="xs"
                                    :tooltip="$knob->getHelperText()"
                                />
                            @endif
                        </div>

                        @if ($isBoolean)
                            <button type="button" class="knob-toggle" wire:click="toggleBooleanKnob('{{ $name }}')" dusk="knob-{{ $name }}-toggle">
                                <span @class(['knob-track', 'on' => $value])>
                                    <span class="knob-thumb"></span>
                                </span>
                                <span class="knob-toggle-label">{{ $value ? 'true' : 'false' }}</span>
                            </button>
                        @elseif ($isSelect)
                            <select class="knob-select" wire:model.live="knobValues.{{ $name }}" dusk="knob-{{ $name }}-select">
                                @foreach ($knob->getOptions() as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                        @else
                            <input
                                class="knob-input"
                                type="{{ $isNumber ? 'number' : 'text' }}"
                                wire:model.live.debounce.150ms="knobValues.{{ $name }}"
                                dusk="knob-{{ $name }}-input"
                                @if ($name === 'suffix')
                                    placeholder="-"
                                @endif
                            />
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</section>
