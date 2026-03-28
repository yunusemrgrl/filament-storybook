@php
    $resolvedHeadline = null;

    foreach (['headline', 'section_title', 'title', 'name'] as $candidate) {
        if (! is_string(${$candidate} ?? null)) {
            continue;
        }

        $value = trim(${$candidate});

        if ($value === '') {
            continue;
        }

        $resolvedHeadline = $value;

        break;
    }

    $resolvedSupportingText = null;

    foreach (['subheadline', 'intro', 'description'] as $candidate) {
        if (! is_string(${$candidate} ?? null)) {
            continue;
        }

        $value = trim(${$candidate});

        if ($value === '') {
            continue;
        }

        $resolvedSupportingText = $value;

        break;
    }

    $resolvedImage = null;

    foreach (['image', 'imagePath'] as $candidate) {
        $value = ${$candidate} ?? null;

        if (is_array($value)) {
            $value = collect($value)
                ->filter(fn (mixed $item): bool => is_string($item) && trim($item) !== '')
                ->first();
        }

        if (! is_string($value)) {
            continue;
        }

        $value = trim($value);

        if ($value === '') {
            continue;
        }

        $resolvedImage = asset('storage/'.$value);

        break;
    }

    $resolvedItems = is_array($items ?? null) ? $items : [];
    $itemsCount = count($resolvedItems);
    $resolvedCta = null;

    foreach (['cta_text', 'button_text'] as $candidate) {
        if (! is_string(${$candidate} ?? null)) {
            continue;
        }

        $value = trim(${$candidate});

        if ($value === '') {
            continue;
        }

        $resolvedCta = $value;

        break;
    }
@endphp

<article class="rounded-2xl border border-gray-200/80 bg-white/95 p-4 shadow-[0_12px_32px_rgba(15,23,42,0.06)] dark:border-white/10 dark:bg-white/[0.04]">
    <div class="flex items-start gap-3">
        @if ($resolvedImage)
            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5">
                <img src="{{ $resolvedImage }}" alt="" class="h-full w-full object-cover">
            </div>
        @else
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 text-[11px] font-medium uppercase tracking-[0.18em] text-gray-400 dark:border-white/15 dark:bg-white/5 dark:text-gray-500">
                Block
            </div>
        @endif

        <div class="min-w-0 flex-1 space-y-2">
            <div class="space-y-1">
                <h3 class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                    {{ $resolvedHeadline ?? 'Untitled block' }}
                </h3>

                @if ($resolvedSupportingText)
                    <p class="line-clamp-2 text-xs leading-5 text-gray-500 dark:text-gray-400">
                        {{ $resolvedSupportingText }}
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($itemsCount > 0)
                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-600 dark:bg-white/5 dark:text-gray-300">
                        {{ $itemsCount }} items
                    </span>
                @endif

                @if ($resolvedCta)
                    <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-medium text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                        CTA: {{ $resolvedCta }}
                    </span>
                @endif

                @if ($resolvedImage)
                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                        Media
                    </span>
                @endif
            </div>
        </div>
    </div>
</article>
