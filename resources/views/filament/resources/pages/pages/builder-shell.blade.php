@php
    $submitMethod = $this->getEditorSubmitMethod();
    $primaryActionLabel = $this->getEditorPrimaryActionLabel();
@endphp

<div>
    <link rel="stylesheet" href="{{ asset('css/storybook-blocks.css') }}">

    <x-filament-panels::page>
        <div class="space-y-6" data-testid="page-builder-shell">
            <x-filament-actions::modals />

            <form wire:submit.prevent="{{ $submitMethod }}" class="space-y-6">
                @include('filament.resources.pages.pages.partials.builder-toolbar', [
                    'primaryActionLabel' => $primaryActionLabel,
                ])

                <div class="grid gap-6 xl:grid-cols-[18rem_minmax(0,1fr)_22rem]">
                    @include('filament.resources.pages.pages.partials.builder-palette')
                    @include('filament.resources.pages.pages.partials.builder-canvas')
                    @include('filament.resources.pages.pages.partials.builder-inspector')
                </div>
            </form>
        </div>
    </x-filament-panels::page>
</div>
