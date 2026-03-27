<section class="docs-card playground-preview-panel">
    <div class="docs-card-head">
        <div>
            <p class="docs-kicker">Preview</p>
            <h2 class="docs-section-title">Live block</h2>
        </div>

        <p class="docs-card-copy">
            Storybook preview ile page builder runtime ayni block viewini paylasir.
        </p>
    </div>

    <div class="playground-preview-toolbar">
        <button type="button" class="docs-action-button is-secondary" wire:click="resetPreview">
            Reset Variant
        </button>
    </div>

    <div class="playground-preview-frame">
        <div class="preview-card preview-card-block" wire:key="block-preview-{{ $this->getPreviewSchemaFingerprint() }}" dusk="block-preview-frame">
            @if ($resolvedBlock)
                @include($resolvedBlock->previewView(), $resolvedBlock->previewData())
            @endif
        </div>
    </div>
</section>
