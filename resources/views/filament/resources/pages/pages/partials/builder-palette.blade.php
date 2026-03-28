@php
    use Illuminate\Support\Str;

    $paletteGroups = $this->getPaletteGroups();
    $structureItems = $this->getEditorStructureBlocks();
@endphp

<aside class="space-y-6">
    <section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-950/5">
        <div class="border-b border-slate-200/80 px-5 py-4">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                Palette
            </p>
            <h2 class="mt-2 text-lg font-semibold text-slate-950">
                Add blocks
            </h2>
            <p class="mt-1 text-sm leading-6 text-slate-600">
                Yalnizca page surface icin aktif olan definition ve sistem block'lari burada listelenir.
            </p>
        </div>

        <div class="space-y-5 p-5">
            <div class="space-y-2">
                <label for="page-builder-palette-search" class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                    Search
                </label>

                <input
                    id="page-builder-palette-search"
                    type="search"
                    wire:model.live.debounce.300ms="paletteSearch"
                    placeholder="Search blocks..."
                    data-testid="page-builder-palette-search"
                    class="block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-amber-400 focus:bg-white focus:ring-2 focus:ring-amber-200"
                >
            </div>

            <div class="space-y-5">
                @forelse ($paletteGroups as $paletteGroup)
                    <section class="space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                {{ $paletteGroup['group'] }}
                            </h3>
                            <span class="text-xs text-slate-400">
                                {{ count($paletteGroup['items']) }}
                            </span>
                        </div>

                        <div class="space-y-2">
                            @foreach ($paletteGroup['items'] as $story)
                                <button
                                    type="button"
                                    wire:click="addBlock('{{ $story->getBlockType() }}')"
                                    data-testid="page-builder-add-{{ Str::slug($story->getBlockType()) }}"
                                    class="flex w-full items-start gap-3 rounded-[1.4rem] border border-slate-200/80 bg-slate-50/70 px-4 py-3 text-left transition hover:border-amber-300 hover:bg-amber-50/70"
                                >
                                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm ring-1 ring-slate-950/5">
                                        <x-filament::icon :icon="$story->icon" class="h-4 w-4" />
                                    </span>

                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-slate-950">
                                            {{ $story->title }}
                                        </span>
                                        <span class="mt-1 block text-xs leading-5 text-slate-500">
                                            {{ $story->description }}
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm leading-6 text-slate-500">
                        Aramaniza uyan page block bulunamadi.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-950/5">
        <div class="border-b border-slate-200/80 px-5 py-4">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                Structure
            </p>
            <h2 class="mt-2 text-lg font-semibold text-slate-950">
                Page outline
            </h2>
        </div>

        <div class="space-y-2 p-5">
            @forelse ($structureItems as $structureItem)
                <button
                    type="button"
                    wire:click="selectBlock('{{ $structureItem['uuid'] }}')"
                    data-testid="page-builder-structure-item"
                    @class([
                        'flex w-full items-start justify-between gap-3 rounded-[1.4rem] border px-4 py-3 text-left transition',
                        'border-amber-300 bg-amber-50/80' => $structureItem['isSelected'],
                        'border-slate-200/80 bg-white hover:border-slate-300 hover:bg-slate-50' => ! $structureItem['isSelected'],
                    ])
                >
                    <span class="min-w-0">
                        <span class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                            {{ $structureItem['group'] }}
                        </span>
                        <span class="mt-1 block truncate text-sm font-medium text-slate-950">
                            {{ $structureItem['title'] }}
                        </span>
                    </span>

                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                        {{ $structureItem['position'] }}
                    </span>
                </button>
            @empty
                <div class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm leading-6 text-slate-500">
                    Ilk block'u soldaki palette'ten ekleyerek canvas'i olusturmaya basla.
                </div>
            @endforelse
        </div>
    </section>
</aside>
