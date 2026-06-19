@php
    $record = $getRecord();
    $urls = collect($record?->photos ?? [])->map(fn ($p) => $p->url)->filter()->values()->all();
@endphp

@if (count($urls))
    <div x-data="{ open: false, src: '' }" class="flex flex-wrap gap-2">
        @foreach ($urls as $url)
            <button
                type="button"
                x-on:click="src = @js($url); open = true"
                class="block overflow-hidden rounded-lg ring-1 ring-gray-200 transition hover:opacity-75 dark:ring-white/10"
            >
                <img src="{{ $url }}" alt="photo" class="h-20 w-20 object-cover" />
            </button>
        @endforeach

        {{-- 點縮圖放大檢視 --}}
        <template x-teleport="body">
            <div
                x-show="open"
                x-on:click="open = false"
                x-on:keydown.escape.window="open = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                style="display: none;"
            >
                <img :src="src" x-on:click.stop alt="photo" class="max-h-[90vh] max-w-[90vw] rounded-lg shadow-2xl" />
            </div>
        </template>
    </div>
@endif
