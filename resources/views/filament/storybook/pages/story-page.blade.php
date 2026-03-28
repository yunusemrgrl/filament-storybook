@php
    use App\Filament\Storybook\AbstractBlockStory;
    use App\Filament\Storybook\AbstractFormStory;
    use App\Filament\Storybook\AbstractKnobStory;

    $storyRoute = 'filament.storybook.pages.story-page';
    $isKnobStory = $story instanceof AbstractKnobStory;
    $isFormStory = $story instanceof AbstractFormStory;
    $isBlockStory = $story instanceof AbstractBlockStory;
    $currentSlug = $story?->getSlug();
    $overviewUrl = $story ? route($storyRoute, ['slug' => $story->getSlug()]) : url('/storybook');
    $defaultPreset = $story?->getDefaultVariantKey();
    $activePresetTitle = $isKnobStory && filled($activePreset)
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
                    <p class="docs-kicker">Meta CMS Lab</p>
                    <h1 class="docs-title">Select a lab story</h1>
                    <p class="docs-description">
                        Primitive field grammarini, sistem blocklarini ve page-surface component definition previewlerini buradan test edin.
                    </p>
                </section>
            @endif
        </main>
    </div>
</div>
