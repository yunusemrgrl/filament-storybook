<div class="sticky top-6" data-testid="page-preview-panel">
    <div class="flex h-[calc(100vh-8rem)] flex-col overflow-hidden rounded-[1.25rem] border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-950">
        <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <div class="min-w-0 space-y-1">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-500 dark:text-gray-400">
                    Live Preview
                </p>
                <h2 class="truncate text-sm font-semibold text-gray-950 dark:text-white" data-testid="page-preview-title">
                    {{ $previewTitle }}
                </h2>
                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 font-medium text-gray-700 dark:bg-white/5 dark:text-gray-200">
                        {{ $previewStatusLabel }}
                    </span>
                    <span class="truncate">
                        /{{ $previewSlug }}
                    </span>
                </div>
            </div>

            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                Auto-sync
            </span>
        </div>

        <div class="flex-1 bg-gray-100 p-3 dark:bg-gray-900/70">
            <iframe
                wire:key="page-preview-frame-{{ $previewVersion }}"
                data-testid="page-preview-frame"
                src="{{ $previewFrameUrl }}"
                title="Page preview"
                class="size-full rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10"
            ></iframe>
        </div>
    </div>
</div>
