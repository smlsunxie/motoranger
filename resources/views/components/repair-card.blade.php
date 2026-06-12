@props(['order', 'tone' => 'red'])

@php
    $photo = $order->vehicle?->photos->first();
    $toneClasses = [
        'red' => ['bar' => 'bg-red-400', 'badge' => 'bg-red-50 text-red-600', 'label' => '維修中'],
        'green' => ['bar' => 'bg-green-400', 'badge' => 'bg-green-50 text-green-600', 'label' => '已完工'],
    ][$tone];
@endphp

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div class="relative aspect-[4/3] bg-slate-100">
        @if($photo)
            <img src="{{ $photo->url }}" alt="{{ $order->vehicle->plate_no }}" loading="lazy"
                 class="h-full w-full object-cover">
        @else
            <div class="flex h-full w-full items-center justify-center text-slate-300">
                <svg class="size-16" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
        @endif
        <span class="absolute right-3 top-3 rounded-full px-3 py-1 text-xs font-bold {{ $toneClasses['badge'] }}">
            {{ $toneClasses['label'] }}
        </span>
    </div>
    <div class="h-1 {{ $toneClasses['bar'] }}"></div>
    <div class="space-y-1.5 p-4 text-sm">
        <p class="truncate font-mono text-xs text-slate-400">{{ $order->order_no }}</p>
        <p class="flex items-center justify-between">
            <span class="text-slate-500">車牌</span>
            <span class="font-bold tracking-wide">{{ $order->vehicle?->plate_no }}</span>
        </p>
        <p class="flex items-center justify-between">
            <span class="text-slate-500">維修人員</span>
            <span class="font-medium">{{ $order->user?->name ?? '—' }}</span>
        </p>
        <p class="flex items-center justify-between">
            <span class="text-slate-500">客戶</span>
            <span class="font-medium text-slate-400">{{ $order->customer?->masked_name }}</span>
        </p>
        <p class="flex items-center justify-between border-t border-slate-100 pt-1.5">
            <span class="text-slate-500">進廠日期</span>
            <span>{{ $order->date->format('Y-m-d') }}</span>
        </p>
    </div>
</div>
