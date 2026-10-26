@props(['title' => null])
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Member Avasport' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50">
    @php
        $customer = auth('customer')->user();
        $nav = [
            ['member.dashboard', 'Dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['member.transactions', 'Transaksi', 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2L19 21z'],
            ['member.points', 'Poin', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
            ['member.profile', 'Profil', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ];
    @endphp

    {{-- Header --}}
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <a href="{{ route('member.dashboard') }}"><x-brand size="sm" /></a>

            <nav class="hidden items-center gap-1 sm:flex">
                @foreach ($nav as [$route, $label, $icon])
                    <a href="{{ route($route) }}"
                       class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($route.'*') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-3">
                <span class="hidden items-center gap-1.5 rounded-full bg-ink-950 px-3 py-1.5 text-sm font-semibold text-white sm:inline-flex">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-400"></span>
                    {{ points_label($customer->points_balance) }}
                </span>
                <form method="POST" action="{{ route('member.logout') }}">
                    @csrf
                    <button class="btn-ghost btn-sm" type="submit">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 pb-28 pt-6 sm:px-6 sm:pb-12">
        <x-flash />
        {{ $slot }}
    </main>

    {{-- Mobile bottom nav --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 flex border-t border-slate-200 bg-white/95 backdrop-blur sm:hidden">
        @foreach ($nav as [$route, $label, $icon])
            <a href="{{ route($route) }}" class="mnav {{ request()->routeIs($route.'*') ? 'mnav-active' : '' }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                {{ $label }}
            </a>
        @endforeach
    </nav>
</body>
</html>
