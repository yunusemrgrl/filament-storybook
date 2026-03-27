<div class="playground-layout">
    @if ($errorMessage)
        <section class="docs-card">
            <div class="docs-empty-state">
                {{ $errorMessage }}
            </div>
        </section>
    @else
        @include('filament.storybook.livewire.partials.preview-panel')
        @include('filament.storybook.livewire.partials.controls-panel')
    @endif
</div>
