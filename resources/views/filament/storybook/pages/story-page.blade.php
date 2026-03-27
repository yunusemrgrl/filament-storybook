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

<div @class([
    'sb-page',
    'docs-page',
    'is-dark' => $isDarkTheme,
]) data-storybook-theme-root data-storybook-theme-default="{{ $theme }}">
    <div class="docs-shell">
        @include('filament.storybook.pages.partials.sidebar')

        <main class="docs-main">
            @if ($story)
                @include('filament.storybook.pages.partials.topbar')

                @if ($isOverview)
                    @include('filament.storybook.pages.partials.overview')
                @else
                    @include('filament.storybook.pages.partials.playground')
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
