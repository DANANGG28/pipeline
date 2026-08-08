<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Kaldera Admin</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⛰️</text></svg>">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/dashboard.js'])
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">
<div class="flex min-h-screen">
    <aside class="hidden w-64 shrink-0 flex-col bg-slate-900 lg:flex">
        <div class="flex items-center gap-3 px-6 py-5">
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-xl">⛰️</div>
            <div>
                <p class="text-sm font-bold text-white">Kaldera Admin</p>
                <p class="text-xs text-slate-400">Toko Outdoor & Camping</p>
            </div>
        </div>
        <nav class="mt-2 flex-1 space-y-1 px-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg bg-indigo-600 px-3 py-2.5 text-sm font-medium text-white">
                <span>📊</span> Dashboard
            </a>
            @if (auth()->user()->is_admin)
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <span>👥</span> Pengguna
                </a>
            @endif
            <a href="#" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-400 transition hover:bg-slate-800 hover:text-white">
                <span>📦</span> Produk <span class="ml-auto rounded bg-slate-700 px-1.5 py-0.5 text-[10px]">40</span>
            </a>
            <a href="#" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-400 transition hover:bg-slate-800 hover:text-white">
                <span>🧾</span> Pesanan
            </a>
            <a href="#" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-400 transition hover:bg-slate-800 hover:text-white">
                <span>👥</span> Pelanggan
            </a>
        </nav>
        <div class="border-t border-slate-800 p-4">
            <div class="flex items-center gap-3">
                <div class="grid h-9 w-9 place-items-center rounded-full bg-slate-700 text-sm font-semibold text-white">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-slate-400">{{ '@' . auth()->user()->username }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="rounded-lg px-2 py-1 text-xs text-slate-400 transition hover:bg-slate-800 hover:text-red-400">Keluar</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:px-8">
            <div>
                <h1 class="text-lg font-bold">@yield('page-header', 'Dashboard')</h1>
                <p class="text-xs text-slate-500" x-data x-text="new Intl.DateTimeFormat('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' }).format(new Date())"></p>
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span class="hidden sm:block">Demo — Data Dummy</span>
                <span class="grid h-8 w-8 cursor-pointer place-items-center rounded-full bg-slate-100">🔔</span>
                <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 lg:hidden">☰</span>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-8">
            @yield('content')
        </main>

        <footer class="border-t border-slate-200 bg-white px-8 py-4 text-center text-xs text-slate-400">
            Toko Kaldera Admin © {{ date('Y') }} — Laravel 12 · Tailwind · Alpine · Chart.js
        </footer>
    </div>
</div>
</body>
</html>
