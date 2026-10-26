<x-layouts.member title="Riwayat Transaksi">
    <h1 class="mb-5 text-2xl font-bold text-slate-900">Riwayat transaksi</h1>

    @if ($transactions->isEmpty())
        <x-empty title="Belum ada transaksi" message="Transaksimu di Avasport akan tampil di sini." />
    @else
        <div class="card-flush divide-y divide-slate-100">
            @foreach ($transactions as $txn)
                <a href="{{ route('member.transactions.show', $txn) }}" class="flex items-center justify-between gap-3 px-5 py-4 hover:bg-slate-50">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $txn->transaction_number }}</p>
                            <x-status-badge :status="$txn->status" />
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">{{ jakarta_date($txn->transaction_date)->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-900">{{ rupiah($txn->final_amount) }}</p>
                        <p class="text-xs {{ $txn->isReversed() ? 'text-red-500' : 'text-emerald-600' }}">
                            @if ($txn->isReversed()) dibatalkan
                            @else +{{ points_label($txn->points_earned) }}@if ($txn->points_redeemed) · −{{ points_label($txn->points_redeemed) }} @endif
                            @endif
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-5">{{ $transactions->links() }}</div>
    @endif
</x-layouts.member>
