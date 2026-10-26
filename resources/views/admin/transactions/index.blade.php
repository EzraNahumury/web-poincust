<x-layouts.admin title="Transaksi" heading="Transaksi">
    @can(\App\Domain\Auth\Permissions::TRANSACTIONS_CREATE)
        <x-slot:actions>
            <a href="{{ route('admin.transactions.create') }}" class="btn-primary btn-sm">+ Tambah Transaksi</a>
        </x-slot:actions>
    @endcan

    <div class="card mb-5">
        <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
            <div class="lg:col-span-2">
                <label class="label">Cari</label>
                <input name="q" value="{{ $search }}" placeholder="No transaksi / nama / member" class="input">
            </div>
            <div>
                <label class="label">Status</label>
                <select name="status" class="input">
                    <option value="">Semua</option>
                    <option value="completed" @selected($status === 'completed')>Selesai</option>
                    <option value="reversed" @selected($status === 'reversed')>Dibatalkan</option>
                </select>
            </div>
            <div><label class="label">Dari</label><input type="date" name="from" value="{{ $from }}" class="input"></div>
            <div><label class="label">Sampai</label><input type="date" name="to" value="{{ $to }}" class="input"></div>
            <div class="flex gap-2 lg:col-span-5">
                <button class="btn-dark" type="submit">Terapkan</button>
                <a href="{{ route('admin.transactions.index') }}" class="btn-ghost">Reset</a>
            </div>
        </form>
    </div>

    <div class="card-flush overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>No / Customer</th><th>Tanggal</th><th class="text-right">Total</th><th class="text-right">Poin (+/−)</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($transactions as $txn)
                        <tr>
                            <td><p class="font-medium text-slate-900">{{ $txn->transaction_number }}</p><p class="text-xs text-slate-500">{{ $txn->customer?->name }}</p></td>
                            <td class="text-xs text-slate-500">{{ jakarta_date($txn->transaction_date)->translatedFormat('d M Y, H:i') }}</td>
                            <td class="text-right font-medium">{{ rupiah($txn->final_amount) }}</td>
                            <td class="text-right text-xs"><span class="text-emerald-600">+{{ $txn->points_earned }}</span>@if ($txn->points_redeemed)<span class="text-red-500"> / −{{ $txn->points_redeemed }}</span>@endif</td>
                            <td><x-status-badge :status="$txn->status" /></td>
                            <td class="text-right"><a href="{{ route('admin.transactions.show', $txn) }}" class="text-sm font-medium text-brand-600">Lihat</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-slate-400">Tidak ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $transactions->links() }}</div>
</x-layouts.admin>
