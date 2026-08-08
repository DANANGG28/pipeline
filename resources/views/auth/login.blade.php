@extends('layouts.auth')

@section('title', 'Login — Kaldera Admin')

@section('content')
<div class="w-full max-w-md">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-3xl shadow-lg shadow-indigo-500/30">⛰️</div>
        <h1 class="text-2xl font-bold text-white">Kaldera Admin</h1>
        <p class="mt-1 text-sm text-slate-400">Masuk ke dashboard Toko Outdoor & Camping</p>
    </div>

    <div class="rounded-2xl border border-slate-700 bg-slate-800/80 p-8 shadow-xl backdrop-blur">
        @if (session('status'))
            <p class="mb-4 rounded-lg bg-emerald-500/10 px-4 py-2 text-sm text-emerald-400">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="username" class="mb-1.5 block text-sm font-medium text-slate-300">Username</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    value="{{ old('username') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="minimal 5 karakter"
                    class="w-full rounded-lg border border-slate-600 bg-slate-900 px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                @error('username')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-300">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="minimal 5 karakter"
                    class="w-full rounded-lg border border-slate-600 bg-slate-900 px-4 py-2.5 text-sm text-white placeholder-slate-500 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30">
                @error('password')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex cursor-pointer items-center gap-2 text-slate-400">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-600 bg-slate-900 accent-indigo-500">
                    Ingat saya
                </label>
            </div>

            <button type="submit" class="w-full rounded-lg bg-gradient-to-r from-indigo-500 to-violet-600 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:opacity-90 active:scale-[0.99]">
                Masuk
            </button>
        </form>
    </div>

    <p class="mt-6 text-center text-xs text-slate-500">Demo: username <code class="rounded bg-slate-800 px-1.5 py-0.5 text-slate-300">admin</code> · password <code class="rounded bg-slate-800 px-1.5 py-0.5 text-slate-300">admin123</code></p>
</div>
@endsection
