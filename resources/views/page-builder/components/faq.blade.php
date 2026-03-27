<section class="sb-block-faq" data-testid="dynamic-faq-block">
    <div class="sb-block-faq__header">
        <span class="sb-block-faq__eyebrow">{{ $componentDefinition->name }}</span>
        <h2 class="sb-block-faq__title">{{ $props['section_title'] ?? '' }}</h2>

        @if (filled($props['intro'] ?? null))
            <p class="sb-block-faq__intro">{{ $props['intro'] }}</p>
        @endif
    </div>

    <div class="sb-block-faq__items">
        @foreach ($props['items'] ?? [] as $item)
            <article class="sb-block-faq__item">
                <h3 class="sb-block-faq__question">{{ $item['question'] ?? '' }}</h3>
                <p class="sb-block-faq__answer">{{ $item['answer'] ?? '' }}</p>
            </article>
        @endforeach
    </div>
</section>
