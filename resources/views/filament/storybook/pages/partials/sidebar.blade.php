@php
    use App\Filament\Storybook\AbstractKnobStory;
@endphp

<aside class="docs-sidebar">
    <div class="docs-sidebar-header">
        <a href="{{ url('/storybook') }}" class="docs-brand">
            <span class="docs-brand-dot"></span>
            <span>Filament Storybook</span>
        </a>

        <p class="docs-sidebar-copy">
            Docs-first component library
        </p>

        <div class="docs-theme-switch">
            <div class="docs-theme-switch-head">
                <span class="docs-theme-label">Appearance</span>
                <span class="docs-theme-value" data-storybook-theme-value>{{ $theme === 'dark' ? 'Dark' : 'Light' }}</span>
            </div>

            <div class="docs-theme-toggle" role="group" aria-label="Storybook theme">
                <button
                    type="button"
                    data-storybook-theme-button="light"
                    aria-pressed="{{ $theme === 'light' ? 'true' : 'false' }}"
                    @class([
                        'docs-theme-button',
                        'is-active' => $theme === 'light',
                    ])
                >
                    Light
                </button>

                <button
                    type="button"
                    data-storybook-theme-button="dark"
                    aria-pressed="{{ $theme === 'dark' ? 'true' : 'false' }}"
                    @class([
                        'docs-theme-button',
                        'is-active' => $theme === 'dark',
                    ])
                >
                    Dark
                </button>
            </div>
        </div>
    </div>

    <div class="docs-sidebar-scroll">
        @forelse ($storyGroups as $groupName => $stories)
            <section class="docs-nav-group">
                <p class="docs-nav-group-label">{{ $groupName }}</p>

                @foreach ($stories as $navStory)
                    @php
                        $isActiveStory = $currentSlug === $navStory->getSlug();
                        $navOverviewUrl = route($storyRoute, ['slug' => $navStory->getSlug()]);
                        $navIsKnobStory = $navStory instanceof AbstractKnobStory;
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

                        @if ($navIsKnobStory)
                            <span class="docs-nav-pill">
                                {{ count($navStory->variants()) }} variants
                            </span>
                        @endif
                    </a>

                    @if ($isActiveStory && $navIsKnobStory)
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
