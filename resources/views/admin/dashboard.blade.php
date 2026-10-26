<x-layouts.admin title="Dashboard" heading="Dashboard">
    @can(\App\Domain\Auth\Permissions::TRANSACTIONS_CREATE)
        <x-slot:actions>
            <a href="{{ route('admin.transactions.create') }}" class="btn-primary btn-sm">+ Tambah Transaksi</a>
        </x-slot:actions>
    @endcan

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Total customer" :value="number_format($totalCustomers, 0, ',', '.')" :sub="$activeCustomers . ' aktif'" accent="ink" />
        <x-stat label="Transaksi hari ini" :value="$todayCount . 'x'" :sub="rupiah($todayAmount)" accent="brand" />
        <x-stat label="Poin diberikan (hari ini)" :value="points_label($pointsEarnedToday)" accent="green" />
        <x-stat label="Poin ditukar (hari ini)" :value="points_label($pointsRedeemedToday)" accent="red" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Recent transactions --}}
        <div class="card-flush overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="font-semibold text-slate-900">Transaksi terbaru</h2>
                @can(\App\Domain\Auth\Permissions::TRANSACTIONS_VIEW)
                    <a href="{{ route('admin.transactions.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Semua</a>
                @endcan
            </div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>No / Customer</th><th>Waktu</th><th class="text-right">Total</th><th class="text-right">Poin</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($recentTransactions as $txn)
                            <tr>
                                <td>
                                    <p class="font-medium text-slate-900">{{ $txn->transaction_number }}</p>
                                    <p class="text-xs text-slate-500">{{ $txn->customer?->name }}</p>
                                </td>
                                <td class="text-xs text-slate-500">{{ jakarta_date($txn->transaction_date)->translatedFormat('d M, H:i') }}</td>
                                <td class="text-right font-medium">{{ rupiah($txn->final_amount) }}</td>
                                <td class="text-right text-xs">
                                    <span class="text-emerald-600">+{{ $txn->points_earned }}</span>
                                    @if ($txn->points_redeemed)<span class="text-red-500"> / −{{ $txn->points_redeemed }}</span>@endif
                                </td>
                                <td class="text-right">
                                    @can(\App\Domain\Auth\Permissions::TRANSACTIONS_VIEW)
                                        <a href="{{ route('admin.transactions.show', $txn) }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Birthdays today --}}
        <div class="card-flush overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="font-semibold text-slate-900">Ulang tahun 🎂</h2>
                @can(\App\Domain\Auth\Permissions::BIRTHDAYS_VIEW)
                    <a href="{{ route('admin.birthdays.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Kelola</a>
                @endcan
            </div>
            <div class="px-5 pb-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hari ini ({{ $birthdaysToday->count() }})</p>
                @forelse ($birthdaysToday->take(5) as $row)
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-sm text-slate-700">{{ $row['customer']->name }}</span>
                        <span class="badge-brand">hari ini</span>
                    </div>
                @empty
                    <p class="mt-2 text-sm text-slate-400">Tidak ada yang berulang tahun hari ini.</p>
                @endforelse

                <p class="mt-5 text-xs font-semibold uppercase tracking-wide text-slate-400">7 hari ke depan ({{ $birthdaysUpcoming->count() }})</p>
                @forelse ($birthdaysUpcoming->take(5) as $row)
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-sm text-slate-700">{{ $row['customer']->name }}</span>
                        <span class="text-xs text-slate-500">{{ jakarta_date($row['birthday_on'])->translatedFormat('d M') }}</span>
                    </div>
                @empty
                    <p class="mt-2 text-sm text-slate-400">Tidak ada dalam 7 hari ke depan.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent activity --}}
    <div class="card-flush mt-6 overflow-hidden">
        <h2 class="px-5 py-4 font-semibold text-slate-900">Aktivitas admin terbaru</h2>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Waktu</th><th>Aktor</th><th>Aksi</th><th>Entitas</th></tr></thead>
                <tbody>
                    @forelse ($recentActivity as $log)
                        <tr>
                            <td class="text-xs text-slate-500">{{ jakarta_date($log->created_at)->translatedFormat('d M, H:i') }}</td>
                            <td class="text-xs"><span class="badge-slate">{{ $log->actor_type }}</span></td>
                            <td class="font-medium text-slate-700">{{ $log->action }}</td>
                            <td class="text-xs text-slate-500">{{ $log->entity_type }}{{ $log->entity_id ? ' #'.$log->entity_id : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">Belum ada aktivitas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin>
