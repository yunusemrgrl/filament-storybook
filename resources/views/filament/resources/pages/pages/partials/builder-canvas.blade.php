@php
    $canvasBlocks = $this->getEditorCanvasBlocks();
@endphp

<section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-950/5">
    <div class="flex flex-col gap-4 border-b border-slate-200/80 px-6 py-5 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                Canvas
            </p>
            <h2 class="mt-2 text-lg font-semibold text-slate-950">
                Live visual preview
            </h2>
            <p class="mt-1 text-sm leading-6 text-slate-600">
                Buradaki goruntu ayni block Blade view'larini admin icinde dogrudan render eder. Preview route yeni sekmede kontrol icin hazir kalir.
            </p>
        </div>

        <div class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-500">
            {{ $this->getEditorRouteLabel() }}
        </div>
    </div>

    <div class="bg-[radial-gradient(circle_at_top,_rgba(251,191,36,0.18),_transparent_32%),linear-gradient(180deg,_#f8fafc,_#eef2ff)] p-5 lg:p-7">
        <div class="mx-auto max-w-5xl overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white shadow-[0_40px_120px_rgba(15,23,42,0.08)]">
            <div class="flex items-center justify-between border-b border-slate-200/80 bg-slate-50/80 px-5 py-3">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-rose-300"></span>
                    <span class="h-3 w-3 rounded-full bg-amber-300"></span>
                    <span class="h-3 w-3 rounded-full bg-emerald-300"></span>
                </div>

                <div class="rounded-full bg-white px-4 py-1 text-xs font-medium text-slate-500 ring-1 ring-slate-950/5">
                    Direct Blade runtime
                </div>
            </div>

            <div class="space-y-6 bg-[#f5f7fb] p-5 lg:p-8" data-testid="page-builder-canvas">
                @forelse ($canvasBlocks as $canvasBlock)
                    <article
                        wire:key="page-builder-canvas-{{ $canvasBlock['uuid'] }}"
                        @class([
                            'group relative overflow-hidden rounded-[1.6rem] border bg-white shadow-[0_12px_32px_rgba(15,23,42,0.05)]',
                            'border-amber-300 ring-2 ring-amber-200' => $canvasBlock['isSelected'],
                            'border-slate-200/80 hover:border-slate-300' => ! $canvasBlock['isSelected'],
                        ])
                        data-testid="page-builder-canvas-block"
                    >
                        <button
                            type="button"
                            wire:click="selectBlock('{{ $canvasBlock['uuid'] }}')"
                            class="absolute inset-0 z-10"
                            aria-label="Select {{ $canvasBlock['label'] }}"
                        ></button>

                        <div class="flex items-center justify-between gap-3 border-b border-slate-200/80 px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-950">
                                    {{ $canvasBlock['label'] }}
                                </p>
                                <p class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-400">
                                    Block {{ $canvasBlock['position'] }}
                                </p>
                            </div>

                            @if ($canvasBlock['isSelected'])
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">
                                    Selected
                                </span>
                            @endif
                        </div>

                        <div class="public-page pointer-events-none bg-white p-4 lg:p-6">
                            @include($canvasBlock['resolved']->frontendView(), $canvasBlock['resolved']->frontendData())
                        </div>
                    </article>
                @empty
                    <div class="grid min-h-[34rem] place-items-center rounded-[1.75rem] border border-dashed border-slate-300 bg-white/80 px-6 py-10 text-center">
                        <div class="max-w-md space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">
                                Empty canvas
                            </p>
                            <h3 class="text-2xl font-semibold tracking-tight text-slate-950">
                                Henüz page block yok
                            </h3>
                            <p class="text-sm leading-7 text-slate-600">
                                Soldaki palette'ten bir block ekle. Canvas secili block'u outline ile isaretler, inspector ise detaylarini modal-first duzenleme akisina aciklar.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
