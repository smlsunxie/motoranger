<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Moto Ranger - 摩托機車線上維修記錄' }}</title>
    <meta name="description" content="Moto Ranger 線上摩托維修記錄,不再忘記何時加機油!支援拍照記錄、估價單列印、QR Code 查詢維修進度。">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold tracking-tight">
                <svg class="size-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 0 1-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 1 1-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 0 1 6.336-4.486l-3.276 3.276a3.004 3.004 0 0 0 2.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852Z"/>
                </svg>
                <span>Moto<span class="text-amber-500">Ranger</span></span>
            </a>
            <div class="flex items-center gap-1 sm:gap-2">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-sm font-medium hover:bg-slate-100">首頁</a>
                <a href="#in-progress" class="hidden rounded-lg px-3 py-2 text-sm font-medium hover:bg-slate-100 sm:block">維修動態</a>
                <a href="{{ url('/admin') }}" class="ml-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600">
                    店家登入
                </a>
            </div>
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-20 bg-slate-900 text-slate-300">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:grid-cols-2 sm:px-6">
            <div>
                <h3 class="mb-4 border-b border-slate-700 pb-2 text-sm font-semibold uppercase tracking-wider text-slate-400">連結</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-amber-400">首頁 <span class="italic text-slate-500">index</span></a></li>
                    <li><a href="#in-progress" class="hover:text-amber-400">維修動態 <span class="italic text-slate-500">status</span></a></li>
                    <li><a href="{{ url('/admin') }}" class="hover:text-amber-400">店家登入 <span class="italic text-slate-500">login</span></a></li>
                </ul>
            </div>
            <div>
                <h3 class="mb-4 border-b border-slate-700 pb-2 text-sm font-semibold uppercase tracking-wider text-slate-400">聯絡我們</h3>
                <p class="text-sm">對系統有任何建議歡迎來信</p>
                <a href="mailto:smlsun@gmail.com" class="mt-2 inline-block rounded bg-slate-800 px-3 py-1.5 text-sm font-medium text-amber-400 hover:bg-slate-700">
                    ✉ smlsun@gmail.com
                </a>
            </div>
        </div>
        <div class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">
            © {{ date('Y') }} Moto Ranger — 摩托機車線上維修記錄
        </div>
    </footer>

</body>
</html>
