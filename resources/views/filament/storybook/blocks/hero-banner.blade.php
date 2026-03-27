<section class="sb-block-hero {{ $block->wrapperClasses() }}">
    <div class="sb-block-hero__panel {{ $block->contentClasses() }}">
        <span class="sb-block-hero__eyebrow">Page Block</span>

        <h2 class="sb-block-hero__headline">{{ $block->headline }}</h2>

        <p class="sb-block-hero__subheadline">{{ $block->subheadline }}</p>

        @if ($block->hasPrimaryAction())
            <a href="{{ $block->primaryCtaUrl }}" class="sb-block-hero__cta">
                {{ $block->primaryCtaText }}
            </a>
        @endif
    </div>
</section>
