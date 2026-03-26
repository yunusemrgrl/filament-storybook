<div class="playground-layout">
    @if ($errorMessage)
        <section class="docs-card">
            <div class="docs-empty-state">
                {{ $errorMessage }}
            </div>
        </section>
    @else
        <section class="docs-card playground-preview-panel">
            <div class="docs-card-head">
                <div>
                    <p class="docs-kicker">Preview</p>
                    <h2 class="docs-section-title">Live component</h2>
                </div>

                <p class="docs-card-copy">
                    Buradaki demo yalnizca secili variant ve knobs kombinasyonunu gosterir.
                </p>
            </div>

            <div class="playground-preview-frame">
                <div class="preview-card">
                    {{ $this->previewForm }}
                </div>
            </div>
        </section>

        <section class="docs-card playground-controls-panel">
            <div class="docs-card-head">
                <div>
                    <p class="docs-kicker">Controls</p>
                    <h2 class="docs-section-title">Knobs</h2>
                </div>

                <p class="docs-card-copy">
                    Knobs, preset ustune cikarak label, helper text ve field state gibi detaylari serbestce test etmenizi saglar.
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
            </div>

            <div class="knobs-grid">
                @foreach ($knobDefinitions as $knob)
                    @php
                        $name = $knob->getName();
                        $value = $knobValues[$name] ?? $knob->getDefault();
                        $isBoolean = $knob->getType() === \App\Filament\Storybook\KnobDefinition::TYPE_BOOLEAN;
                        $isNumber = $knob->getType() === \App\Filament\Storybook\KnobDefinition::TYPE_NUMBER;
                    @endphp

                    <div class="knob-field" wire:key="knob-{{ $name }}">
                        <div class="knob-label">
                            <span>{{ $knob->getLabel() }}</span>

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
                            <button type="button" class="knob-toggle" wire:click="toggleBooleanKnob('{{ $name }}')">
                                <span @class(['knob-track', 'on' => $value])>
                                    <span class="knob-thumb"></span>
                                </span>
                                <span class="knob-toggle-label">{{ $value ? 'true' : 'false' }}</span>
                            </button>
                        @else
                            <input
                                class="knob-input"
                                type="{{ $isNumber ? 'number' : 'text' }}"
                                wire:model.live.debounce.150ms="knobValues.{{ $name }}"
                                @if ($name === 'suffix')
                                    placeholder="-"
                                @endif
                            />
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
