@php
    $vehicle = $this->record;
    $orders = $this->getOrders();
    $unpaid = $orders->sum(fn ($o) => max(0, (int) $o->total - (int) $o->paid_amount));
    $cover = $vehicle->photos->first()?->url
        ?? optional($orders->first())->photos?->first()?->url;
@endphp

<x-filament-panels::page>
    {{-- 上半:外觀 / 車輛資料 / 車主資料 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- 外觀:點擊可換照片 / 直接拍照 --}}
        <x-filament::section>
            <x-slot name="heading">外觀</x-slot>

            <button
                type="button"
                wire:click="mountAction('rephoto')"
                title="點擊更換 / 拍照"
                class="group relative block w-full overflow-hidden rounded-lg"
            >
                @if ($cover)
                    <img src="{{ $cover }}" alt="{{ $vehicle->plate_no }}" class="aspect-square w-full object-cover" />
                @else
                    <div class="flex aspect-square w-full items-center justify-center bg-gray-50 text-gray-300 dark:bg-white/5">
                        <x-filament::icon icon="heroicon-o-truck" class="h-20 w-20" />
                    </div>
                @endif

                <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/45 text-sm font-medium text-white opacity-0 transition group-hover:opacity-100">
                    <x-filament::icon icon="heroicon-o-camera" class="h-5 w-5" />
                    點擊更換 / 拍照
                </div>
            </button>
        </x-filament::section>

        {{-- 車輛資料 --}}
        <x-filament::section>
            <x-slot name="heading">車輛資料</x-slot>

            <dl class="divide-y divide-gray-100 text-sm dark:divide-white/10">
                @foreach ([
                    '車牌' => $vehicle->plate_no,
                    '廠牌' => $vehicle->brand?->name,
                    '車型' => $vehicle->model,
                    '排氣量' => $vehicle->cc,
                    '里程數' => $vehicle->mileage ? number_format($vehicle->mileage).' km' : null,
                    '年份' => $vehicle->year,
                    '顏色' => $vehicle->color,
                    '總未收金額' => '$ '.number_format($unpaid),
                    '簡述' => $vehicle->description,
                ] as $label => $value)
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                        <dd class="text-right font-medium text-gray-950 dark:text-white">{{ filled($value) ? $value : '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-filament::section>

        {{-- 車主資料 --}}
        <x-filament::section>
            <x-slot name="heading">車主資料</x-slot>

            <dl class="divide-y divide-gray-100 text-sm dark:divide-white/10">
                @foreach ([
                    '姓名' => $vehicle->customer?->name,
                    '手機' => $vehicle->customer?->mobile,
                    '電話' => $vehicle->customer?->phone,
                    'Email' => $vehicle->customer?->email,
                    '地址' => $vehicle->customer?->address,
                    '備註' => $vehicle->customer?->description,
                ] as $label => $value)
                    <div class="flex justify-between gap-4 py-2">
                        <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                        <dd class="text-right font-medium text-gray-950 dark:text-white">{{ filled($value) ? $value : '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-filament::section>
    </div>

    {{-- 維修記錄 --}}
    <x-filament::section>
        <x-slot name="heading">維修記錄</x-slot>
        <x-slot name="description">共 {{ $orders->count() }} 筆</x-slot>

        @if ($orders->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">此車輛尚無維修紀錄。</p>
        @else
            <div class="space-y-4">
                @foreach ($orders as $order)
                    <div class="grid grid-cols-1 gap-4 rounded-xl border border-gray-200 p-4 md:grid-cols-12 dark:border-white/10">
                        {{-- 左:單據摘要 --}}
                        <div class="md:col-span-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ \App\Filament\Resources\RepairOrders\RepairOrderResource::getUrl('edit', ['record' => $order]) }}"
                                   class="font-semibold text-primary-600 hover:underline dark:text-primary-400">
                                    {{ $order->order_no }}
                                </a>
                                <x-filament::badge :color="$order->status->getColor()">{{ $order->status->getLabel() }}</x-filament::badge>
                                <a href="{{ route('repair-orders.quote', $order) }}"
                                   target="_blank"
                                   title="列印估價單"
                                   class="text-gray-400 transition hover:text-primary-600 dark:hover:text-primary-400">
                                    <x-filament::icon icon="heroicon-o-printer" class="h-5 w-5" />
                                </a>
                            </div>
                            <dl class="mt-3 space-y-1 text-sm">
                                <div class="flex gap-2"><dt class="text-gray-500 dark:text-gray-400">記錄里程</dt><dd class="font-medium">{{ number_format($order->mileage) }} km</dd></div>
                                <div class="flex gap-2"><dt class="text-gray-500 dark:text-gray-400">維修人員</dt><dd class="font-medium">{{ $order->user?->name ?? '—' }}</dd></div>
                                <div class="flex gap-2"><dt class="text-gray-500 dark:text-gray-400">維修店家</dt><dd class="font-medium">{{ $order->store?->name ?? '—' }}</dd></div>
                                <div class="flex gap-2"><dt class="text-gray-500 dark:text-gray-400">建立日期</dt><dd class="font-medium">{{ $order->date?->format('Y-m-d') }}</dd></div>
                            </dl>
                        </div>

                        {{-- 右:維修項目 --}}
                        <div class="md:col-span-8">
                            @if ($order->items->isEmpty())
                                <p class="text-sm text-gray-400">無維修項目</p>
                            @else
                                <table class="w-full text-sm">
                                    <thead class="text-gray-500 dark:text-gray-400">
                                        <tr class="border-b border-gray-100 dark:border-white/10">
                                            <th class="py-1 text-left font-medium">維修項目</th>
                                            <th class="py-1 text-right font-medium">數量</th>
                                            <th class="py-1 text-right font-medium">售價</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                                        @foreach ($order->items as $item)
                                            <tr>
                                                <td class="py-1">{{ $item->name }}</td>
                                                <td class="py-1 text-right">{{ $item->qty }}</td>
                                                <td class="py-1 text-right">$ {{ number_format($item->price) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-t border-gray-200 font-semibold dark:border-white/10">
                                            <td class="py-1" colspan="2">應收金額</td>
                                            <td class="py-1 text-right">$ {{ number_format($order->total) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
