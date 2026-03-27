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
        @if ($isKnobStory && $isOverview && filled($defaultPreset))
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
