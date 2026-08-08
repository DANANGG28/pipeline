@extends('layouts.app')

@section('title', 'Pengguna — Kaldera Admin')
@section('page-header', 'Pengguna')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold">Manajemen Pengguna</h2>
            <p class="text-sm text-slate-500">{{ $users->count() }} akun terdaftar · hanya admin</p>
        </div>
        <button
            @click="open = true"
            class="rounded-lg bg-gradient-to-r from-indigo-500 to-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:opacity-90">
            + Tambah Pengguna
        </button>
    </div>

    @if (session('success'))
        <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</p>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                        <th class="px-6 py-3 font-medium">Pengguna</th>
                        <th class="px-6 py-3 font-medium">Username</th>
                        <th class="px-6 py-3 font-medium">Email</th>
                        <th class="px-6 py-3 font-medium">Peran</th>
                        <th class="px-6 py-3 font-medium">Terdaftar</th>
                        <th class="px-6 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-b border-slate-50 hover:bg-slate-50/70">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                    <span class="font-medium">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 font-mono text-xs text-slate-600">{{ $user->username }}</td>
                            <td class="px-6 py-3 text-slate-600">{{ $user->email }}</td>
                            <td class="px-6 py-3">
                                @if ($user->is_admin)
                                    <span class="rounded-full bg-violet-100 px-2 py-1 text-[11px] font-semibold text-violet-700">Admin</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-600">Staf</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs text-slate-500">{{ $user->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-3 text-right">
                                @if ($user->is(auth()->user()))
                                    <span class="text-xs text-slate-300">—</span>
                                @elseif ($user->is_admin)
                                    <span class="text-xs text-slate-300">Admin</span>
                                @else
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" x-data @submit.prevent="if (confirm('Hapus pengguna {{ $user->username }}?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal tambah pengguna --}}
    <div x-data="{ open: false }" @keydown.escape.window="open = false" x-cloak>
        <div x-show="open" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/60 p-4" @click.self="open = false">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold">Tambah Pengguna</h3>
                    <button @click="open = false" class="grid h-8 w-8 place-items-center rounded-full text-slate-400 transition hover:bg-slate-100">✕</button>
                </div>

                <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/20">
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">Username <span class="text-slate-400">(min. 5 karakter)</span></label>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" required minlength="5"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/20">
                        @error('username') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/20">
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password <span class="text-slate-400">(min. 5)</span></label>
                            <input id="password" name="password" type="password" required minlength="5"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/20">
                            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Ulangi</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="5"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
