<section class="sb-block-hero {{ $block->wrapperClasses() }}" data-testid="hero-banner-block">
    <div class="sb-block-hero__panel">
        <div class="sb-block-hero__copy {{ $block->contentClasses() }}">
            <span class="sb-block-hero__eyebrow">Page Block</span>

            <h2 class="sb-block-hero__headline">{{ $block->headline }}</h2>

            <p class="sb-block-hero__subheadline">{{ $block->subheadline }}</p>

            @if ($block->hasPrimaryAction())
                <a href="{{ $block->primaryCtaUrl }}" class="sb-block-hero__cta">
                    {{ $block->primaryCtaText }}
                </a>
            @endif
        </div>

        <div class="sb-block-hero__media">
            @if ($block->hasImage())
                <img
                    src="{{ $block->imageUrl() }}"
                    alt="{{ $block->imageAlt }}"
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
