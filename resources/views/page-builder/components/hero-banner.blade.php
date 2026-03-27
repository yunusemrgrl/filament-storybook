@php
    $textAlign = $props['text_align'] ?? 'left';
    $contentClasses = match ($textAlign) {
        'center' => 'sb-block-hero__copy is-align-center',
        'right' => 'sb-block-hero__copy is-align-right',
        default => 'sb-block-hero__copy is-align-left',
    };
@endphp

<section class="sb-block-hero" data-testid="dynamic-hero-banner-block">
    <div class="sb-block-hero__panel">
        <div class="{{ $contentClasses }}">
            <span class="sb-block-hero__eyebrow">{{ $componentDefinition->name }}</span>

            <h2 class="sb-block-hero__headline">{{ $props['headline'] ?? '' }}</h2>

            @if (filled($props['subheadline'] ?? null))
                <p class="sb-block-hero__subheadline">{{ $props['subheadline'] }}</p>
            @endif

            @if (filled($props['cta_text'] ?? null) && filled($props['cta_url'] ?? null))
                <a href="{{ $props['cta_url'] }}" class="sb-block-hero__cta">
                    {{ $props['cta_text'] }}
                </a>
            @endif
        </div>

        <div class="sb-block-hero__media">
            @if (filled($props['image'] ?? null))
                <img
                    src="{{ asset('storage/'.$props['image']) }}"
                    alt="{{ $props['image_alt'] ?? ($props['headline'] ?? $componentDefinition->name) }}"
                    class="sb-block-hero__image"
                >
            @else
                <div class="sb-block-hero__image-placeholder">
                    <span>Upload a hero image</span>
                </div>
            @endif
        </div>
    </div>
</section>
