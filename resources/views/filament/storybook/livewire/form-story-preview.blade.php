<div class="variant-preview-shell">
    @if ($errorMessage)
        <div class="docs-empty-state">
            {{ $errorMessage }}
        </div>
    @else
        <div class="variant-preview-card">
            {{ $this->previewForm }}
        </div>
    @endif
</div>
