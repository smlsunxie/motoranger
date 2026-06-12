<x-front-layout>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-slate-900">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(245,158,11,0.18),transparent_55%)]"></div>
        <div class="relative mx-auto flex max-w-6xl flex-col items-start gap-6 px-4 py-24 sm:px-6 lg:py-32">
            <p class="rounded-full border border-amber-500/40 bg-amber-500/10 px-4 py-1 text-sm font-medium text-amber-400">
                Moto Ranger
            </p>
            <h1 class="text-4xl font-bold leading-tight tracking-tight text-white sm:text-5xl">
                線上摩托維修記錄<br>
                <span class="text-amber-400">不再忘記何時加機油!</span>
            </h1>
            <p class="max-w-xl text-lg text-slate-300">
                替每一台車留下完整的維修歷程 — 拍照記錄、估價單列印、掃 QR Code 即時查看維修進度。
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="#in-progress" class="rounded-xl bg-amber-500 px-6 py-3 font-semibold text-slate-900 shadow-lg shadow-amber-500/25 transition hover:bg-amber-400">
                    查看維修動態
                </a>
                <a href="{{ url('/admin') }}" class="rounded-xl border border-slate-600 px-6 py-3 font-semibold text-slate-200 transition hover:border-slate-400 hover:text-white">
                    店家登入
                </a>
            </div>
        </div>
    </section>

    {{-- 特色 --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['icon' => 'M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z', 'title' => '拍照記錄', 'desc' => '維修前後直接用手機拍照上傳,照片跟著維修單與車輛保存,歷程一目了然。'],
                ['icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z', 'title' => '自定維修記錄', 'desc' => '每個人常用的維修項目皆不一樣,可依你的習慣建立零件與項目,快速帶入價格。'],
                ['icon' => 'M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z', 'title' => '個人與店家適用', 'desc' => '每次客人都說沒多久前才維修過?替你的客戶留下最真實的維修歷程。'],
                ['icon' => 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z', 'title' => '估價單 + QR Code', 'desc' => '一鍵列印估價單給客人,掃描 QR Code 即可查看維修明細並留言備注。'],
            ] as $feature)
            <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="mb-4 inline-flex rounded-xl bg-amber-50 p-3 text-amber-500 transition group-hover:bg-amber-500 group-hover:text-white">
                    <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-bold">{{ $feature['title'] }}</h3>
                <p class="text-sm leading-relaxed text-slate-500">{{ $feature['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- 維修中 --}}
    <section id="in-progress" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-8 sm:px-6">
        <h2 class="mb-6 flex items-center gap-2 text-2xl font-bold">
            <span class="inline-flex size-9 items-center justify-center rounded-lg bg-red-100 text-red-500">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/>
                </svg>
            </span>
            維修中
        </h2>
        @if($inProgress->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-400">目前沒有維修中的車輛</p>
        @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($inProgress as $order)
                <x-repair-card :order="$order" tone="red" />
            @endforeach
        </div>
        @endif
    </section>

    {{-- 維修完成 --}}
    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <h2 class="mb-6 flex items-center gap-2 text-2xl font-bold">
            <span class="inline-flex size-9 items-center justify-center rounded-lg bg-green-100 text-green-600">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
            </span>
            維修完成
        </h2>
        @if($completed->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-400">尚無完成的維修記錄</p>
        @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($completed as $order)
                <x-repair-card :order="$order" tone="green" />
            @endforeach
        </div>
        @endif
    </section>

</x-front-layout>
