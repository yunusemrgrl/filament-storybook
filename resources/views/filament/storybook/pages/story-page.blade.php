@php
    use App\Filament\Storybook\AbstractFormStory;

    $storyRoute = 'filament.storybook.pages.story-page';
    $isFormStory = $story instanceof AbstractFormStory;
    $currentSlug = $story?->getSlug();
    $overviewUrl = $story ? route($storyRoute, ['slug' => $story->getSlug()]) : url('/storybook');
    $defaultPreset = $story?->getDefaultVariantKey();
    $activePresetTitle = $isFormStory && filled($activePreset)
        ? $story->getPresetTitle($activePreset)
        : null;
@endphp

<div class="sb-page docs-page">
    <div class="docs-shell">
        <aside class="docs-sidebar">
            <div class="docs-sidebar-header">
                <a href="{{ url('/storybook') }}" class="docs-brand">
                    <span class="docs-brand-dot"></span>
                    <span>Filament Storybook</span>
                </a>

                <p class="docs-sidebar-copy">
                    Docs-first component library
                </p>
            </div>

            <div class="docs-sidebar-scroll">
                @forelse ($storyGroups as $groupName => $stories)
                    <section class="docs-nav-group">
                        <p class="docs-nav-group-label">{{ $groupName }}</p>

                        @foreach ($stories as $navStory)
                            @php
                                $isActiveStory = $currentSlug === $navStory->getSlug();
                                $navOverviewUrl = route($storyRoute, ['slug' => $navStory->getSlug()]);
                                $navIsFormStory = $navStory instanceof AbstractFormStory;
                            @endphp

                            <a
                                href="{{ $navOverviewUrl }}"
                                wire:navigate
                                @class([
                                    'docs-nav-item',
                                    'is-active' => $isActiveStory && $isOverview,
                                ])
                            >
                                <span>{{ $navStory->title }}</span>

                                @if ($navIsFormStory)
                                    <span class="docs-nav-pill">
                                        {{ count($navStory->variants()) }} variants
                                    </span>
                                @endif
                            </a>

                            @if ($isActiveStory && $navIsFormStory)
                                <div class="docs-nav-children">
                                    @foreach ($navStory->variants() as $presetKey)
                                        <a
                                            href="{{ route($storyRoute, ['slug' => $navStory->getSlug(), 'preset' => $presetKey]) }}"
                                            wire:navigate
                                            @class([
                                                'docs-nav-child',
                                                'is-active' => ! $isOverview && $activePreset === $presetKey,
                                            ])
                                        >
                                            {{ $navStory->getPresetTitle($presetKey) }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </section>
                @empty
                    <div class="docs-empty-state">
                        Henuz kayitli bir story yok.
                    </div>
                @endforelse
            </div>
        </aside>

        <main class="docs-main">
            @if ($story)
                <div class="docs-topbar">
                    <div class="docs-breadcrumbs">
                        <span>{{ $story->group }}</span>
                        <span>/</span>
                        <span>{{ $story->title }}</span>

                        @if (! $isOverview && filled($activePresetTitle))
                            <span>/</span>
                            <span class="is-current">{{ $activePresetTitle }}</span>
                        @endif
                    </div>

                    <div class="docs-topbar-actions">
                        @if ($isFormStory && $isOverview && filled($defaultPreset))
                            <a
                                href="{{ route($storyRoute, ['slug' => $story->getSlug(), 'preset' => $defaultPreset]) }}"
                                wire:navigate
                                class="docs-primary-link"
                            >
                                Open Playground
                            </a>
                        @elseif (! $isOverview)
                            <a
                                href="{{ $overviewUrl }}"
                                wire:navigate
                                class="docs-secondary-link"
                            >
                                Back to Overview
                            </a>
                        @endif

                        @if (filled($story->getExternalDocsUrl()))
                            <a
                                href="{{ $story->getExternalDocsUrl() }}"
                                target="_blank"
                                rel="noreferrer"
                                class="docs-secondary-link"
                            >
                                Filament Docs
                            </a>
                        @endif
                    </div>
                </div>

                @if ($isOverview)
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
                @else
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
                @endif
            @else
                <section class="docs-hero">
                    <p class="docs-kicker">Storybook</p>
                    <h1 class="docs-title">Select a component</h1>
                    <p class="docs-description">
                        Sol taraftaki navigation alanindan bir component secerek overview ya da playground ekranina gecin.
                    </p>
                </section>
            @endif
        </main>
    </div>
</div>
