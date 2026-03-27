<section class="sb-block-grid {{ $block->wrapperClasses() }}">
    <div class="sb-block-grid__header">
        <div>
            <span class="sb-block-grid__eyebrow">{{ $block->collectionLabel }}</span>
            <h2 class="sb-block-grid__headline">{{ $block->headline }}</h2>
            <p class="sb-block-grid__subheadline">{{ $block->subheadline }}</p>
        </div>
    </div>

    <div class="sb-block-grid__items {{ $block->gridClasses() }}">
        @foreach ($block->products as $product)
            <article class="sb-block-grid__card">
                <div class="sb-block-grid__media"></div>
                <div class="sb-block-grid__copy">
                    <span class="sb-block-grid__category">{{ $product['category'] }}</span>
                    <h3 class="sb-block-grid__name">{{ $product['name'] }}</h3>

                    @if ($block->showPrices)
                        <p class="sb-block-grid__price">{{ $product['price'] }}</p>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</section>
