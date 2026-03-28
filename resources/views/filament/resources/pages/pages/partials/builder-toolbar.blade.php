@php
    $statusOptions = \App\PageStatus::options();
    $rawStatusValue = $this->data['status'] ?? \App\PageStatus::Draft->value;
    $statusValue = $rawStatusValue instanceof \App\PageStatus ? $rawStatusValue->value : $rawStatusValue;
    $statusLabel = $statusOptions[$statusValue] ?? 'Draft';
@endphp

<section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-950/5">
    <div class="flex flex-col gap-5 border-b border-slate-200/80 px-6 py-6 xl:flex-row xl:items-start xl:justify-between">
        <div class="space-y-3">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                Meta CMS Editor
            </p>

            <div class="space-y-2">
                <h1 class="text-3xl font-semibold tracking-tight text-slate-950">
                    {{ trim((string) ($this->data['title'] ?? '')) !== '' ? $this->data['title'] : 'Untitled page' }}
                </h1>

                <p class="max-w-3xl text-sm leading-6 text-slate-600">
                    Page-surface component definitions soldaki palette'ten gelir. Canvas ayni Blade runtime view'larini render eder; inspector ise secili block icin modal-first duzenleme akisina baglanir.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <x-filament::button
                tag="a"
                color="gray"
                :href="$this->getPagePreviewUrl()"
                target="_blank"
                icon="heroicon-o-arrow-top-right-on-square"
                data-testid="page-builder-open-preview"
            >
                Open preview
            </x-filament::button>

            <x-filament::button
                type="button"
                color="success"
                icon="heroicon-o-rocket-launch"
                wire:click="publishPage"
                data-testid="page-builder-publish"
            >
                Publish
            </x-filament::button>

            <x-filament::button
                type="submit"
                icon="heroicon-o-check"
                data-testid="page-builder-save"
            >
                {{ $primaryActionLabel }}
            </x-filament::button>
        </div>
    </div>

    <div class="grid gap-6 px-6 py-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
        <div data-testid="page-builder-meta-form">
            {{ $this->form }}
        </div>

        <aside class="rounded-[1.5rem] border border-slate-200/80 bg-slate-50/80 p-5">
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                            Route
                        </p>
                        <p class="mt-1 text-sm font-medium text-slate-900" data-testid="page-builder-route-label">
                            {{ $this->getEditorRouteLabel() }}
                        </p>
                    </div>

                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600">
                        {{ $statusLabel }}
                    </span>
                </div>

                <dl class="grid gap-3 text-sm text-slate-600">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="font-medium text-slate-500">Surface</dt>
                        <dd class="text-right text-slate-900">Page</dd>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <dt class="font-medium text-slate-500">Render mode</dt>
                        <dd class="text-right text-slate-900">Direct Blade canvas</dd>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <dt class="font-medium text-slate-500">Preview sync</dt>
                        <dd class="text-right text-slate-900">Session-backed preview route</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
</section>
