<div class="variant-preview-shell">
    @if ($errorMessage)
        <div class="docs-empty-state">
            {{ $errorMessage }}
        </div>
    @elseif ($resolvedBlock)
        <div class="variant-preview-card preview-card-block">
            @include($resolvedBlock->previewView(), $resolvedBlock->previewData())
        </div>
    @endif
</div>
