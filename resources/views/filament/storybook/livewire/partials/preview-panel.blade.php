<section class="docs-card playground-preview-panel">
    <div class="docs-card-head">
        <div>
            <p class="docs-kicker">Preview</p>
            <h2 class="docs-section-title">Live component</h2>
        </div>

        <p class="docs-card-copy">
            Buradaki demo yalnizca secili variant ve knobs kombinasyonunu gosterir.
        </p>
    </div>

    <div class="playground-preview-toolbar">
        <button type="button" class="docs-action-button is-secondary" wire:click="resetPreview">
            Reset Value
        </button>
    </div>

    <div class="playground-preview-frame">
        <div class="preview-card" wire:key="preview-schema-{{ $this->getPreviewSchemaFingerprint() }}">
            {{ $this->previewForm }}
        </div>
    </div>
</section>
