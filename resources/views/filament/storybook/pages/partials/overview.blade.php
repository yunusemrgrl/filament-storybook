<section class="docs-hero">
    <p class="docs-kicker">{{ $story->group }} component</p>
    <h1 class="docs-title">{{ $story->title }}</h1>
    <p class="docs-description">{{ $story->description }}</p>

    @if ($isFormStory && filled($defaultPreset))
        <div class="docs-hero-actions">
            <a
                href="{{ route($storyRoute, ['slug' => $story->getSlug(), 'preset' => $defaultPreset]) }}"
                wire:navigate
                class="docs-primary-link"
            >
                Explore Playground
            </a>

            <a href="#variants" class="docs-secondary-link">
                Jump to Variants
            </a>
        </div>
    @endif
</section>

<div class="docs-overview-grid">
    @if (filled($story->getUsageSnippet()))
        <section class="docs-card">
            <div class="docs-card-head">
                <div>
                    <p class="docs-kicker">Usage</p>
                    <h2 class="docs-section-title">Quick start</h2>
                </div>

                <p class="docs-card-copy">
                    Copy-paste ile baslamak isteyenler icin en kisa kurulum.
                </p>
            </div>

            <pre class="docs-code-block"><code>{{ trim($story->getUsageSnippet()) }}</code></pre>
        </section>
    @endif

    @if ($story->anatomy() !== [])
        <section class="docs-card">
            <div class="docs-card-head">
                <div>
                    <p class="docs-kicker">Anatomy</p>
                    <h2 class="docs-section-title">What makes up the field</h2>
                </div>

                <p class="docs-card-copy">
                    Label, helper text ve affix gibi parcaciklarin gorev dagilimi.
                </p>
            </div>

            <div class="docs-anatomy-grid">
                @foreach ($story->anatomy() as $anatomyItem)
                    <article class="docs-anatomy-item">
                        <h3>{{ $anatomyItem['title'] }}</h3>
                        <p>{{ $anatomyItem['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>

@if ($isFormStory && $presets !== [])
    <section id="variants" class="docs-section">
        <div class="docs-section-head">
            <div>
                <p class="docs-kicker">Variants</p>
                <h2 class="docs-section-title">Component gallery</h2>
            </div>

            <p class="docs-section-copy">
                Her kart, docs icindeki belirli bir kullanim senaryosunu gosterir. Kart acildiginda ayni preset icin playground ekranina gecersiniz.
            </p>
        </div>

        <div class="variant-gallery">
            @foreach ($presets as $presetKey)
                @php
                    $presetUrl = route($storyRoute, ['slug' => $story->getSlug(), 'preset' => $presetKey]);
                    $presetCode = $story->getPresetCode($presetKey);
                @endphp

                <article class="variant-card">
                    <div class="variant-card-head">
                        <div>
                            <h3>{{ $story->getPresetTitle($presetKey) }}</h3>
                            <p>{{ $story->getPresetDescription($presetKey) }}</p>
                        </div>

                        <a href="{{ $presetUrl }}" wire:navigate class="docs-secondary-link">
                            Playground
                        </a>
                    </div>

                    @livewire(
                        'story-form-preview',
                        ['slug' => $slug, 'preset' => $presetKey],
                        key("story-preview-{$slug}-{$presetKey}")
                    )

                    @if (filled($presetCode))
                        <pre class="docs-code-block is-compact"><code>{{ trim($presetCode) }}</code></pre>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endif

@if ($story->documentationSections() !== [])
    <section class="docs-section">
        <div class="docs-section-head">
            <div>
                <p class="docs-kicker">Details</p>
                <h2 class="docs-section-title">Implementation notes</h2>
            </div>

            <p class="docs-section-copy">
                Filament dokumantasyonundaki ana basliklari, component library icindeki karar anlarina gore ozetliyoruz.
            </p>
        </div>

        <div class="docs-feature-grid">
            @foreach ($story->documentationSections() as $section)
                <article class="docs-card docs-feature-card">
                    <div class="docs-feature-copy">
                        <h3>{{ $section['title'] }}</h3>
                        <p>{{ $section['description'] }}</p>
                    </div>

                    @if (filled($section['code'] ?? null))
                        <pre class="docs-code-block is-compact"><code>{{ trim($section['code']) }}</code></pre>
                    @endif

                    @if (! empty($section['points'] ?? []))
                        <ul class="docs-list">
                            @foreach ($section['points'] as $point)
                                <li>{{ $point }}</li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endif
