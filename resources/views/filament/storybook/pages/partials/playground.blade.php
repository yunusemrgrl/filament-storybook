<section class="docs-hero is-compact">
    <p class="docs-kicker">Playground</p>
    <h1 class="docs-title">{{ $activePresetTitle }}</h1>
    <p class="docs-description">
        {{ $isFormStory ? $story->getPresetDescription($activePreset) : $story->description }}
    </p>
</section>

@if ($isFormStory)
    <nav class="docs-variant-tabs">
        @foreach ($presets as $presetKey)
            <a
                href="{{ route($storyRoute, ['slug' => $story->getSlug(), 'preset' => $presetKey]) }}"
                wire:navigate
                @class([
                    'docs-variant-tab',
                    'is-active' => $activePreset === $presetKey,
                ])
            >
                {{ $story->getPresetTitle($presetKey) }}
            </a>
        @endforeach
    </nav>

    <div class="docs-callout">
        <strong>Variant vs knobs</strong>
        <p>
            Variant secimi, dokumante edilmis gercek kullanim senaryosunu yukler. Knobs ise bu secimin ustune yalnizca demo icin gecici denemeler ekler.
        </p>
    </div>

    <div class="playground-meta-grid">
        <section class="docs-card">
            <div class="docs-card-head">
                <div>
                    <p class="docs-kicker">Selected variant</p>
                    <h2 class="docs-section-title">Code</h2>
                </div>

                <p class="docs-card-copy">
                    Preset secildiginde yuklenen temel API.
                </p>
            </div>

            @if (filled($story->getPresetCode($activePreset)))
                <pre class="docs-code-block"><code>{{ trim($story->getPresetCode($activePreset)) }}</code></pre>
            @endif
        </section>

        <section class="docs-card">
            <div class="docs-card-head">
                <div>
                    <p class="docs-kicker">Why this variant</p>
                    <h2 class="docs-section-title">Focus points</h2>
                </div>

                <p class="docs-card-copy">
                    Bu ekranin gostermek istedigi esas kararlar.
                </p>
            </div>

            @if ($story->getPresetPoints($activePreset) !== [])
                <ul class="docs-list">
                    @foreach ($story->getPresetPoints($activePreset) as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endif

@if ($renderType === 'form')
    @livewire(
        'story-form-renderer',
        ['slug' => $slug, 'preset' => $activePreset],
        key("story-renderer-{$slug}-{$activePreset}")
    )
@else
    <section class="docs-card">
        <div class="docs-empty-state">
            "{{ $renderType }}" render motoru henuz eklenmedi.
        </div>
    </section>
@endif
