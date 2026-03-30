<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div>
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $title }}</h3>

                @if (filled($runtimeClass ?? null))
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ class_basename($runtimeClass) }}</p>
                @endif
            </div>

            @if (($summary ?? []) !== [])
                <dl class="grid gap-3 sm:grid-cols-2">
                    @foreach ($summary as $key => $value)
                        @continue($value === null || $value === '')

                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-white/5">
                            <dt class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-gray-500 dark:text-gray-400">
                                {{ \Illuminate\Support\Str::headline((string) $key) }}
                            </dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                @if (is_array($value))
                                    {{ json_encode($value, JSON_THROW_ON_ERROR) }}
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">No compiled summary was available for this widget node.</p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
