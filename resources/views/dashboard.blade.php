@extends('layouts.app')

@section('content')
<div x-data="dashboard()" x-init="init()" class="space-y-6">

    {{-- Filter bar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <span class="inline-flex h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
            <span>Data real-time simulasi</span>
        </div>
        <div class="flex rounded-lg bg-slate-100 p-1 text-xs font-medium">
            <template x-for="r in ranges" :key="r.value">
                <button
                    @click="setRange(r.value)"
                    :class="range === r.value ? 'bg-white text-indigo-600 shadow' : 'text-slate-500 hover:text-slate-700'"
                    class="rounded-md px-3 py-1.5 transition"
                    x-text="r.label"></button>
            </template>
        </div>
    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <template x-for="k in kpis" :key="k.key">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-500" x-text="k.label"></p>
                    <span class="grid h-10 w-10 place-items-center rounded-xl text-lg" :class="k.bg" x-text="k.icon"></span>
                </div>
                <p class="mt-3 text-2xl font-bold tracking-tight" x-text="k.value"></p>
                <p class="mt-1 text-xs" :class="k.subClass" x-text="k.sub"></p>
            </div>
        </template>
    </div>

    {{-- Charts row 1 --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold">Penjualan per Bulan</h2>
                    <p class="text-xs text-slate-400" x-text="rangeLabel + ' · klik legenda untuk lihat angka'"></p>
                </div>
                <div class="flex rounded-lg bg-slate-100 p-1 text-xs font-medium">
                    <button @click="chartType='bar'" :class="chartType==='bar' ? 'bg-white text-indigo-600 shadow' : 'text-slate-500'" class="rounded-md px-3 py-1.5">Bar</button>
                    <button @click="chartType='line'" :class="chartType==='line' ? 'bg-white text-indigo-600 shadow' : 'text-slate-500'" class="rounded-md px-3 py-1.5">Line</button>
                </div>
            </div>
            <div class="h-72"><canvas id="monthlyChart"></canvas></div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Penjualan per Kategori</h2>
            <p class="mb-2 text-xs text-slate-400">Berdasarkan nominal</p>
            <div class="h-72"><canvas id="categoryChart"></canvas></div>
        </div>
    </div>

    {{-- Charts row 2 --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Metode Pembayaran</h2>
            <p class="mb-2 text-xs text-slate-400">Distribusi nominal pembayaran</p>
            <div class="h-64"><canvas id="methodChart"></canvas></div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Top 5 Produk Terlaris</h2>
            <p class="mb-2 text-xs text-slate-400">Berdasarkan jumlah unit terjual</p>
            <div class="h-64"><canvas id="topChart"></canvas></div>
        </div>
    </div>

    {{-- Tables --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
                <div>
                    <h2 class="font-semibold">Pesanan Terbaru</h2>
                    <p class="text-xs text-slate-400">10 pesanan terakhir semua periode</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <input
                        x-model="search"
                        type="text"
                        placeholder="Cari nomor / pelanggan..."
                        class="w-44 rounded-lg border border-slate-200 px-3 py-1.5 text-xs outline-none focus:border-indigo-400">
                    <select x-model="statusFilter" class="rounded-lg border border-slate-200 px-2 py-1.5 text-xs outline-none focus:border-indigo-400">
                        <option value="">Semua status</option>
                        <template x-for="(s, key) in statusMap" :key="key">
                            <option :value="key" x-text="s"></option>
                        </template>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-3 font-medium">Pesanan</th>
                            <th class="px-5 py-3 font-medium">Pelanggan</th>
                            <th class="px-5 py-3 font-medium">Total</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Pembayaran</th>
                            <th class="px-5 py-3 font-medium">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="o in filteredOrders" :key="o.order_number">
                            <tr class="border-b border-slate-50 hover:bg-slate-50/70">
                                <td class="px-5 py-3 font-mono text-xs font-medium text-indigo-600" x-text="o.order_number"></td>
                                <td class="px-5 py-3">
                                    <p class="font-medium" x-text="o.customer"></p>
                                    <p class="text-xs text-slate-400" x-text="o.city"></p>
                                </td>
                                <td class="px-5 py-3 font-medium" x-text="fmt(o.total)"></td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2 py-1 text-[11px] font-semibold" :class="statusClass(o.status)" x-text="statusMap[o.status]"></span>
                                </td>
                                <td class="px-5 py-3 text-xs" x-text="methodLabel(o.method)"></td>
                                <td class="px-5 py-3 text-xs text-slate-500" x-text="o.date"></td>
                            </tr>
                        </template>
                        <tr x-show="filteredOrders.length === 0">
                            <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-400">Tidak ada pesanan yang cocok.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <h2 class="font-semibold">Stok Menipis</h2>
                <p class="text-xs text-slate-400">Produk dengan stok ≤ 5 unit</p>
            </div>
            <ul class="divide-y divide-slate-50">
                <template x-for="p in lowStock" :key="p.id">
                    <li class="flex items-center gap-3 px-5 py-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-slate-100 text-lg" x-text="p.image"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium" x-text="p.name"></p>
                            <p class="text-xs text-slate-400" x-text="p.category + ' · terjual ' + p.sold"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold" x-text="fmt(p.price)"></p>
                            <span
                                class="inline-block rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="p.stock === 0 ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700'"
                                x-text="p.stock === 0 ? 'Habis' : 'Sisa ' + p.stock"></span>
                        </div>
                    </li>
                </template>
                <li x-show="lowStock.length === 0" class="px-5 py-8 text-center text-sm text-slate-400">Semua stok aman.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
