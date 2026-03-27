<section class="docs-card playground-controls-panel">
    <div class="docs-card-head">
        <div>
            <p class="docs-kicker">Controls</p>
            <h2 class="docs-section-title">Knobs</h2>
        </div>

        <p class="docs-card-copy">
            Knobs yalnizca secili variant ile uyumlu ayarlari acarak anlamsiz kombinasyonlari gizler.
        </p>
    </div>

    <div class="playground-explainer">
        <div class="playground-explainer-item">
            <span class="playground-explainer-label">Variant</span>
            <span>Dokumanda referans olarak gosterdigimiz kurgu.</span>
        </div>

        <div class="playground-explainer-item">
            <span class="playground-explainer-label">Knobs</span>
            <span>Bu kurgunun ustune eklenen gecici playground ayarlari.</span>
        </div>

        <div class="playground-explainer-item">
            <span class="playground-explainer-label">Levels</span>
            <span>Prototype primitive davranisi, Component recipe katmanini, Page ise block seviyesini anlatir.</span>
        </div>
    </div>

    <div class="knobs-form-shell">
        {{ $this->knobsForm }}
    </div>
</section>
