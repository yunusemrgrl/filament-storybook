<x-filament-panels::page>
    <section class="space-y-6" data-testid="dashboard-builder-shell">
        <div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-950/5">
            <div class="flex flex-col gap-5 border-b border-slate-200/80 px-6 py-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                        Dashboard Surface
                    </p>
                    <div class="space-y-2">
                        <h1 class="text-3xl font-semibold tracking-tight text-slate-950">
                            Dashboard Builder
                        </h1>
                        <p class="max-w-3xl text-sm leading-6 text-slate-600">
                            Bu sayfa, gelecekte definition-driven dashboard engine icin kullanilacak widget palette, canvas ve inspector kabugunu dogrular. Bu milestone'da persistence yok; yalnizca shell davranisi ve surface kontrati sabitlenir.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        disabled
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-medium text-slate-400"
                    >
                        Save disabled
                    </button>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[18rem_minmax(0,1fr)_22rem]">
            <aside class="space-y-6">
                <section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-950/5">
                    <div class="border-b border-slate-200/80 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                            Widget palette
                        </p>
                        <h2 class="mt-2 text-lg font-semibold text-slate-950">
                            Add widgets
                        </h2>
                    </div>

                    <div class="space-y-5 p-5">
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="paletteSearch"
                            placeholder="Search widgets..."
                            data-testid="dashboard-builder-search"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-400 focus:bg-white focus:ring-2 focus:ring-amber-200"
                        >

                        <div class="space-y-5">
                            @foreach ($this->getPaletteGroups() as $group)
                                <section class="space-y-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                            {{ $group['group'] }}
                                        </h3>
                                        <span class="text-xs text-slate-400">{{ count($group['items']) }}</span>
                                    </div>

                                    <div class="space-y-2">
                                        @foreach ($group['items'] as $widget)
                                            <button
                                                type="button"
                                                wire:click="addWidget('{{ $widget['key'] }}')"
                                                data-testid="dashboard-builder-add-widget-{{ $widget['key'] }}"
                                                class="flex w-full items-start gap-3 rounded-[1.4rem] border border-slate-200/80 bg-slate-50/70 px-4 py-3 text-left transition hover:border-amber-300 hover:bg-amber-50/70"
                                            >
                                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm ring-1 ring-slate-950/5">
                                                    <x-filament::icon :icon="$widget['icon']" class="h-4 w-4" />
                                                </span>

                                                <span class="min-w-0">
                                                    <span class="block text-sm font-medium text-slate-950">
                                                        {{ $widget['title'] }}
                                                    </span>
                                                    <span class="mt-1 block text-xs leading-5 text-slate-500">
                                                        {{ $widget['description'] }}
                                                    </span>
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </section>
            </aside>

            <section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-950/5">
                <div class="flex flex-col gap-4 border-b border-slate-200/80 px-6 py-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                            Dashboard canvas
                        </p>
                        <h2 class="mt-2 text-lg font-semibold text-slate-950">
                            Widget composition
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Page builder canvas ile ayni editor dili kullanilir; fark sadece dashboard surface'in widget-first olmasidir.
                        </p>
                    </div>

                    <div class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-500">
                        Placeholder shell
                    </div>
                </div>

                <div class="bg-[radial-gradient(circle_at_top,_rgba(251,191,36,0.18),_transparent_32%),linear-gradient(180deg,_#f8fafc,_#eef2ff)] p-5 lg:p-7">
                    <div class="grid gap-4 lg:grid-cols-2" data-testid="dashboard-builder-canvas">
                        @foreach ($this->getCanvasWidgets() as $widget)
                            <article
                                @class([
                                    'rounded-[1.6rem] border bg-white p-5 shadow-[0_12px_32px_rgba(15,23,42,0.05)] transition',
                                    'border-amber-300 ring-2 ring-amber-200' => $widget['key'] === $this->selectedWidgetKey,
                                    'border-slate-200/80 hover:border-slate-300' => $widget['key'] !== $this->selectedWidgetKey,
                                ])
                                data-testid="dashboard-builder-widget-card"
                            >
                                <button
                                    type="button"
                                    wire:click="selectWidget('{{ $widget['key'] }}')"
                                    class="w-full text-left"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                                {{ $widget['group'] }}
                                            </p>
                                            <h3 class="mt-2 text-lg font-semibold text-slate-950">
                                                {{ $widget['title'] }}
                                            </h3>
                                        </div>

                                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                            <x-filament::icon :icon="$widget['icon']" class="h-5 w-5" />
                                        </span>
                                    </div>

                                    <div class="mt-6 flex items-end justify-between gap-4">
                                        <div>
                                            <p class="text-3xl font-semibold tracking-tight text-slate-950">
                                                {{ $widget['metric'] }}
                                            </p>
                                            <p class="mt-2 text-sm text-emerald-600">
                                                {{ $widget['trend'] }}
                                            </p>
                                        </div>

                                        <div class="h-16 w-24 rounded-2xl bg-[linear-gradient(180deg,_rgba(251,191,36,0.24),_rgba(251,191,36,0.02))]"></div>
                                    </div>
                                </button>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-950/5">
                    <div class="border-b border-slate-200/80 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                            Inspector
                        </p>
                        <h2 class="mt-2 text-lg font-semibold text-slate-950">
                            {{ $this->getSelectedWidget()['title'] ?? 'Select a widget' }}
                        </h2>
                    </div>

                    <div class="space-y-5 p-5">
                        @if ($selectedWidget = $this->getSelectedWidget())
                            <p class="text-sm leading-6 text-slate-600">
                                {{ $selectedWidget['description'] }}
                            </p>

                            <dl class="grid gap-3 rounded-[1.5rem] border border-slate-200/80 bg-slate-50/80 p-4 text-sm text-slate-600">
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="font-medium text-slate-500">Surface</dt>
                                    <dd class="text-right text-slate-900">Dashboard</dd>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="font-medium text-slate-500">Type</dt>
                                    <dd class="text-right text-slate-900">{{ $selectedWidget['key'] }}</dd>
                                </div>
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="font-medium text-slate-500">State</dt>
                                    <dd class="text-right text-slate-900">Non-persistent placeholder</dd>
                                </div>
                            </dl>

                            <button
                                type="button"
                                wire:click="removeSelectedWidget"
                                data-testid="dashboard-builder-remove-widget"
                                class="inline-flex w-full items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 transition hover:border-rose-300 hover:bg-rose-100"
                            >
                                Remove widget
                            </button>
                        @else
                            <div class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm leading-6 text-slate-500">
                                Canvas uzerinden bir widget sec. Bu shell dashboard surface icin ileride gelecek definition-driven editorun yerlesim dilini sabitler.
                            </div>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    </section>
</x-filament-panels::page>
