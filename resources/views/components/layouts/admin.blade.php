@props(['title' => 'Admin Avasport', 'heading' => null])

@php
    $user = auth('web')->user();
    use App\Domain\Auth\Permissions;

    $groups = [
        ['Operasional', [
            ['admin.dashboard', 'Dashboard', null, 'M3 12l2-2 7-7 7 7 2 2M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10'],
            ['admin.customers.index', 'Customer', Permissions::CUSTOMERS_VIEW, 'M15 19a6 6 0 00-12 0M9 11a4 4 0 100-8 4 4 0 000 8zM21 19a5 5 0 00-4-4.9'],
            ['admin.transactions.index', 'Transaksi', Permissions::TRANSACTIONS_VIEW, 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2L19 21z'],
            ['admin.birthdays.index', 'Ulang Tahun', Permissions::BIRTHDAYS_VIEW, 'M12 8c1.1 0 2-.9 2-2 0-.38-.1-.73-.29-1.03L12 2l-1.71 2.97c-.19.3-.29.65-.29 1.03 0 1.1.9 2 2 2zM5 10h14v4a2 2 0 01-2 2H7a2 2 0 01-2-2v-4zM4 21h16'],
        ]],
        ['Analitik', [
            ['admin.reports.index', 'Laporan', Permissions::REPORTS_VIEW, 'M4 19V5m0 14h16M8 15v-4m4 4V9m4 6v-6'],
            ['admin.audit.index', 'Audit Log', Permissions::AUDIT_VIEW, 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ]],
        ['Pengaturan', [
            ['admin.settings.edit', 'Program Poin', Permissions::SETTINGS_MANAGE, 'M10.3 3.3a1 1 0 011.4 0l.9.9a1 1 0 00.8.3 1 1 0 011 1 1 1 0 00.4.8l.9.9a1 1 0 010 1.4M4 12a8 8 0 1016 0 8 8 0 00-16 0zm8-3a3 3 0 100 6 3 3 0 000-6z'],
            ['admin.users.index', 'Admin & Role', Permissions::USERS_MANAGE, 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197'],
        ]],
    ];

    $linkClass = fn ($route) => 'side-link' . (request()->routeIs($route.'*') ? ' side-link-active' : '');
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · Admin Avasport</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-100">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full overflow-y-auto bg-ink-950 px-4 py-5 transition-transform lg:static lg:translate-x-0">
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}"><x-brand tone="light" size="sm" /></a>
                <button type="button" onclick="toggleSidebar()" class="rounded-lg p-1 text-ink-200 hover:bg-white/10 lg:hidden">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="mt-1 px-1 text-[11px] font-medium uppercase tracking-wider text-ink-400">Panel Admin</p>

            <nav class="mt-5 space-y-6">
                @foreach ($groups as [$groupLabel, $items])
                    @php $visible = collect($items)->filter(fn ($i) => $i[2] === null || $user->hasPermission($i[2])); @endphp
                    @if ($visible->isNotEmpty())
                        <div>
                            <p class="mb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-ink-400">{{ $groupLabel }}</p>
                            <div class="space-y-1">
                                @foreach ($visible as [$route, $label, $perm, $icon])
                                    <a href="{{ route($route) }}" class="{{ $linkClass($route) }}">
                                        <svg class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>
        </aside>

        {{-- Mobile overlay --}}
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 z-30 hidden bg-ink-950/50 lg:hidden"></div>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 py-3 sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleSidebar()" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-bold text-slate-900">{{ $heading ?? $title }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold leading-tight text-slate-800">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $user->role?->label ?? '—' }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="btn-secondary btn-sm" type="submit">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6">
                <x-flash />
                @isset($actions)
                    <div class="mb-5 flex flex-wrap items-center justify-end gap-2">{{ $actions }}</div>
                @endisset
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }
    </script>
</body>
</html>
