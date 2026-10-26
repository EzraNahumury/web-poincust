@php use App\Domain\Auth\Permissions; @endphp
<x-layouts.admin title="Detail Transaksi" :heading="$transaction->transaction_number">
    <div class="mx-auto max-w-3xl space-y-6">
        <a href="{{ route('admin.transactions.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg> Kembali
        </a>

        <div class="card">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Customer</p>
                    <a href="{{ route('admin.customers.show', $transaction->customer) }}" class="text-lg font-bold text-brand-700 hover:underline">{{ $transaction->customer->name }}</a>
                    <p class="text-sm text-slate-500">{{ $transaction->customer->member_number }}</p>
                </div>
                <div class="text-right">
                    <x-status-badge :status="$transaction->status" />
                    <p class="mt-1 text-xs text-slate-500">{{ jakarta_date($transaction->transaction_date)->translatedFormat('d M Y, H:i') }}</p>
                    <p class="text-xs text-slate-400">oleh {{ $transaction->creator?->name ?? 'sistem' }}</p>
                </div>
            </div>

            @if ($transaction->isReversed())
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold">Transaksi dibatalkan</p>
                    <p class="mt-1">Alasan: {{ $transaction->reversal_reason }}</p>
                    <p class="text-xs text-red-500">oleh {{ $transaction->reversedBy?->name }} · {{ jakarta_date($transaction->reversed_at)->translatedFormat('d M Y, H:i') }}</p>
                </div>
            @endif

            <dl class="mt-6 space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Total belanja</dt><dd class="font-medium">{{ rupiah($transaction->gross_amount) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Diskon transaksi</dt><dd class="font-medium">− {{ rupiah($transaction->transaction_discount) }}</dd></div>
                <div class="flex justify-between border-t border-dashed border-slate-200 pt-3"><dt class="text-slate-500">Belanja memenuhi syarat</dt><dd class="font-medium">{{ rupiah($transaction->eligible_amount) }}</dd></div>
                @if ($transaction->points_redeemed)
                    <div class="flex justify-between"><dt class="text-slate-500">Diskon poin ({{ $transaction->points_redeemed }} poin)</dt><dd class="font-medium text-red-600">− {{ rupiah($transaction->redemption_discount) }}</dd></div>
                @endif
                <div class="flex justify-between border-t border-slate-200 pt-3 text-base"><dt class="font-semibold text-slate-700">Total dibayar</dt><dd class="font-bold text-slate-900">{{ rupiah($transaction->final_amount) }}</dd></div>
            </dl>

            <div class="mt-5 grid grid-cols-2 gap-4">
                <div class="rounded-xl bg-emerald-50 p-4"><p class="text-xs text-emerald-700">Poin diperoleh</p><p class="text-lg font-bold text-emerald-700">+{{ $transaction->points_earned }}</p></div>
                <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Poin ditukar</p><p class="text-lg font-bold text-slate-700">{{ $transaction->points_redeemed ? '−'.$transaction->points_redeemed : '—' }}</p></div>
            </div>

            @if ($transaction->notes)
                <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-600"><p class="font-medium text-slate-700">Catatan</p><p class="mt-1">{{ $transaction->notes }}</p></div>
            @endif
        </div>

        {{-- Ledger --}}
        <div class="card-flush overflow-hidden">
            <h3 class="px-5 py-4 font-semibold text-slate-900">Mutasi poin terkait</h3>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Waktu</th><th>Tipe</th><th>Keterangan</th><th class="text-right">Perubahan</th><th class="text-right">Saldo</th></tr></thead>
                    <tbody>
                        @forelse ($transaction->ledgerEntries as $l)
                            <tr>
                                <td class="text-xs text-slate-500">{{ jakarta_date($l->created_at)->translatedFormat('d M, H:i') }}</td>
                                <td class="text-xs"><span class="badge-slate">{{ $l->type }}</span></td>
                                <td class="text-slate-600">{{ $l->description }}</td>
                                <td class="text-right font-semibold {{ $l->points_delta >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $l->points_delta >= 0 ? '+' : '' }}{{ $l->points_delta }}</td>
                                <td class="text-right text-slate-500">{{ $l->balance_after }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-slate-400">Tidak ada mutasi poin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Reversal --}}
        @can(Permissions::TRANSACTIONS_REVERSE)
            @unless ($transaction->isReversed())
                <div class="card border-red-100">
                    <h3 class="font-semibold text-red-700">Batalkan transaksi</h3>
                    <p class="mt-1 text-sm text-slate-500">Membuat reversal yang mengembalikan poin sesuai aturan. Riwayat asli tetap tersimpan untuk audit.</p>
                    <form method="POST" action="{{ route('admin.transactions.reverse', $transaction) }}" class="mt-4 space-y-3" onsubmit="return confirm('Yakin membatalkan transaksi ini? Tindakan ini tidak dapat diurungkan.')">
                        @csrf
                        <x-input name="reason" label="Alasan pembatalan" />
                        <button type="submit" class="btn-danger">Batalkan Transaksi</button>
                    </form>
                </div>
            @endunless
        @endcan
    </div>
</x-layouts.admin>
