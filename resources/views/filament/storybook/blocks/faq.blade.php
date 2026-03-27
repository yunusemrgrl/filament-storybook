<section class="sb-block-faq {{ $block->wrapperClasses() }}" data-testid="faq-block">
    <div class="sb-block-faq__header">
        <span class="sb-block-faq__eyebrow">Support</span>
        <h2 class="sb-block-faq__title">{{ $block->sectionTitle }}</h2>

        @if ($block->hasIntro())
            <p class="sb-block-faq__intro">{{ $block->introText }}</p>
        @endif
    </div>

    <div class="sb-block-faq__items">
        @foreach ($block->items as $item)
            <article class="sb-block-faq__item">
                <h3 class="sb-block-faq__question">{{ $item['question'] }}</h3>
                <p class="sb-block-faq__answer">{{ $item['answer'] }}</p>
            </article>
        @endforeach
    </div>
</section>
