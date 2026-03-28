<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $state = $getState();
        $fileUrl = null;
        $fileName = null;

        if ($state instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            $fileName = $state->getClientOriginalName();

            if ($field->isImageUpload()) {
                $fileUrl = $state->temporaryUrl();
            }
        } elseif (is_string($state) && ($state !== '')) {
            $fileName = basename($state);

            if ($field->isImageUpload()) {
                $fileUrl = \Illuminate\Support\Facades\Storage::disk($field->getDisk() ?? 'public')->url($state);
            }
        }
    @endphp

    <div {{ $getExtraAttributeBag()->class('space-y-3') }}>
        <input
            type="file"
            accept="{{ $field->isImageUpload() ? 'image/*' : '*/*' }}"
            {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
            @foreach ($field->getInputAttributes() as $attribute => $value)
                {{ $attribute }}="{{ $value }}"
            @endforeach
            class="block w-full cursor-pointer rounded-xl border border-gray-300 bg-white px-3 py-3 text-sm text-gray-900 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-white/10 dark:bg-gray-900 dark:text-white dark:file:bg-white dark:file:text-gray-900"
        />

        <div wire:loading wire:target="{{ $getStatePath() }}" class="rounded-xl border border-dashed border-primary-300 bg-primary-50 px-3 py-2 text-xs text-primary-700 dark:border-primary-500/40 dark:bg-primary-500/10 dark:text-primary-200">
            Uploading file...
        </div>

        @if ($fileUrl)
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5">
                <img src="{{ $fileUrl }}" alt="{{ $fileName ?? 'Uploaded image preview' }}" class="h-48 w-full object-cover" />
            </div>
        @elseif ($fileName)
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                {{ $fileName }}
            </div>
        @endif
    </div>
</x-dynamic-component>
