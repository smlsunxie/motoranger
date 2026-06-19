<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-magnifying-glass"
        icon-color="primary"
    >
        <x-slot name="heading">維修檔案查詢</x-slot>
        <x-slot name="description">輸入部分車牌(或車主姓名 / 手機),按 Enter 或「搜尋」。</x-slot>

        <form wire:submit="submitSearch" class="flex gap-2">
            <x-filament::input.wrapper class="flex-1">
                <x-filament::input
                    type="text"
                    wire:model="search"
                    placeholder="例如:ABC 或 180CPP"
                    autofocus
                />
            </x-filament::input.wrapper>

            <x-filament::button type="submit" icon="heroicon-m-magnifying-glass">
                搜尋
            </x-filament::button>

            @if ($searched)
                <x-filament::button type="button" color="gray" wire:click="clearSearch">
                    清除
                </x-filament::button>
            @endif
        </form>

        @if ($searched)
            @php
                $results = $this->results;
            @endphp

            @if ($results->isEmpty())
                <div class="mt-6 flex flex-col items-start gap-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        找不到符合「{{ $search }}」的車輛。
                    </p>
                    {{ $this->createVehicleAction }}
                </div>
            @else
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($results as $vehicle)
                        @php
                            $order = $vehicle->latestRepairOrder;
                            $cover = $vehicle->photos->first()?->url ?? $order?->photos?->first()?->url;
                            $href = \App\Filament\Resources\Vehicles\VehicleResource::getUrl('profile', ['record' => $vehicle]);
                        @endphp

                        <a href="{{ $href }}"
                           wire:key="vehicle-{{ $vehicle->id }}"
                           class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-white/10 dark:bg-gray-900">
                            {{-- 圖片 / 狀態 --}}
                            <div class="relative aspect-[4/3] bg-gray-50 dark:bg-white/5">
                                @if ($cover)
                                    <img src="{{ $cover }}" alt="{{ $vehicle->plate_no }}" class="h-full w-full object-cover" />
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-gray-300">
                                        <x-filament::icon icon="heroicon-o-truck" class="h-16 w-16" />
                                    </div>
                                @endif

                                @if ($order)
                                    <div class="absolute right-2 top-2">
                                        <x-filament::badge :color="$order->status->getColor()">
                                            {{ $order->status->getLabel() }}
                                        </x-filament::badge>
                                    </div>
                                @endif
                            </div>

                            {{-- 資訊 --}}
                            <div class="border-t-2 border-primary-500 p-3 text-sm">
                                <div class="truncate text-xs text-gray-400">{{ $order?->order_no ?? '尚無維修紀錄' }}</div>
                                <dl class="mt-2 space-y-1">
                                    <div class="flex justify-between gap-2">
                                        <dt class="text-gray-500 dark:text-gray-400">車牌</dt>
                                        <dd class="font-semibold text-gray-950 dark:text-white">{{ $vehicle->plate_no }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <dt class="text-gray-500 dark:text-gray-400">維修人員</dt>
                                        <dd class="font-medium">{{ $order?->user?->name ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <dt class="text-gray-500 dark:text-gray-400">客戶</dt>
                                        <dd class="font-medium">{{ $vehicle->customer?->name ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <dt class="text-gray-500 dark:text-gray-400">進廠日期</dt>
                                        <dd class="font-medium">{{ $order?->date?->format('Y-m-d') ?? '—' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif
    </x-filament::section>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
