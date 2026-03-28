@php
    $selectedMeta = $this->getSelectedBlockMeta();
@endphp

<aside class="space-y-6" data-testid="page-builder-inspector">
    <section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-950/5">
        <div class="border-b border-slate-200/80 px-5 py-4">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                Inspector
            </p>
            <h2 class="mt-2 text-lg font-semibold text-slate-950">
                {{ $this->getSelectedBlockTitle() }}
            </h2>
            <p class="mt-1 text-sm leading-6 text-slate-600">
                {{ $this->getSelectedBlockDescription() }}
            </p>
        </div>

        <div class="space-y-5 p-5">
            @if ($this->hasSelectedBlock())
                <div class="grid gap-3 sm:grid-cols-2">
                    <button
                        type="button"
                        wire:click="mountAction('editBlock')"
                        data-testid="page-builder-edit-block"
                        class="inline-flex items-center justify-center rounded-2xl bg-amber-500 px-4 py-3 text-sm font-medium text-slate-950 transition hover:bg-amber-400"
                    >
                        Edit block
                    </button>

                    <button
                        type="button"
                        wire:click="duplicateSelectedBlock"
                        data-testid="page-builder-duplicate-block"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                    >
                        Duplicate
                    </button>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <button
                        type="button"
                        wire:click="moveSelectedBlockUp"
                        data-testid="page-builder-move-block-up"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                    >
                        Move up
                    </button>

                    <button
                        type="button"
                        wire:click="moveSelectedBlockDown"
                        data-testid="page-builder-move-block-down"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                    >
                        Move down
                    </button>
                </div>

                <button
                    type="button"
                    wire:click="removeSelectedBlock"
                    data-testid="page-builder-remove-block"
                    class="inline-flex w-full items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 transition hover:border-rose-300 hover:bg-rose-100"
                >
                    Remove block
                </button>

                <dl class="grid gap-3 rounded-[1.5rem] border border-slate-200/80 bg-slate-50/80 p-4 text-sm text-slate-600">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="font-medium text-slate-500">Type</dt>
                        <dd class="text-right text-slate-900">{{ $selectedMeta['type'] ?? '—' }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <dt class="font-medium text-slate-500">Group</dt>
                        <dd class="text-right text-slate-900">{{ $selectedMeta['group'] ?? '—' }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <dt class="font-medium text-slate-500">Position</dt>
                        <dd class="text-right text-slate-900">
                            {{ $selectedMeta['position'] ?? '—' }}
                            @if (($selectedMeta['total'] ?? 0) > 0)
                                / {{ $selectedMeta['total'] }}
                            @endif
                        </dd>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <dt class="font-medium text-slate-500">Schema fields</dt>
                        <dd class="text-right text-slate-900">{{ $selectedMeta['fields'] }}</dd>
                    </div>
                </dl>

                <div class="space-y-3 rounded-[1.5rem] border border-slate-200/80 bg-white p-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                            Prop keys
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @forelse ($selectedMeta['keys'] as $key)
                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                {{ $key }}
                            </span>
                        @empty
                            <span class="text-sm text-slate-500">
                                Bu block icin kayitli prop yok.
                            </span>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm leading-6 text-slate-500">
                    Canvas veya sol outline listesinden bir block sec. Inspector sadece o block'a ait metadata ve aksiyonlari gosterir.
                </div>
            @endif
        </div>
    </section>
</aside>
