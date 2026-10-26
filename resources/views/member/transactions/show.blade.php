<x-layouts.member title="Detail Transaksi">
    <a href="{{ route('member.transactions') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>

    <div class="card">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Nomor transaksi</p>
                <h1 class="text-xl font-bold text-slate-900">{{ $transaction->transaction_number }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ jakarta_date($transaction->transaction_date)->translatedFormat('l, d F Y · H:i') }} WIB</p>
            </div>
            <x-status-badge :status="$transaction->status" />
        </div>

        @if ($transaction->isReversed())
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Transaksi ini telah dibatalkan. Poin terkait sudah dikembalikan sesuai aturan pembatalan.
            </div>
        @endif

        <dl class="mt-6 space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Total belanja</dt><dd class="font-medium text-slate-900">{{ rupiah($transaction->gross_amount) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Diskon transaksi</dt><dd class="font-medium text-slate-900">− {{ rupiah($transaction->transaction_discount) }}</dd></div>
            <div class="flex justify-between border-t border-dashed border-slate-200 pt-3"><dt class="text-slate-500">Belanja memenuhi syarat</dt><dd class="font-medium text-slate-900">{{ rupiah($transaction->eligible_amount) }}</dd></div>
            @if ($transaction->points_redeemed)
                <div class="flex justify-between"><dt class="text-slate-500">Poin ditukar ({{ points_label($transaction->points_redeemed) }})</dt><dd class="font-medium text-red-600">− {{ rupiah($transaction->redemption_discount) }}</dd></div>
            @endif
            <div class="flex justify-between border-t border-slate-200 pt-3 text-base"><dt class="font-semibold text-slate-700">Total dibayar</dt><dd class="font-bold text-slate-900">{{ rupiah($transaction->final_amount) }}</dd></div>
        </dl>

        <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="rounded-xl bg-emerald-50 p-4">
                <p class="text-xs text-emerald-700">Poin diperoleh</p>
                <p class="text-lg font-bold text-emerald-700">+{{ points_label($transaction->points_earned) }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Poin ditukar</p>
                <p class="text-lg font-bold text-slate-700">{{ $transaction->points_redeemed ? '−' . points_label($transaction->points_redeemed) : '—' }}</p>
            </div>
        </div>

        @if ($transaction->notes)
            <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                <p class="font-medium text-slate-700">Catatan</p>
                <p class="mt-1">{{ $transaction->notes }}</p>
            </div>
        @endif
    </div>

    @if ($transaction->ledgerEntries->isNotEmpty())
        <div class="card-flush mt-6 overflow-hidden">
            <h2 class="px-5 py-4 font-semibold text-slate-900">Mutasi poin transaksi ini</h2>
            @foreach ($transaction->ledgerEntries as $entry)
                @include('member.partials.ledger-row', ['entry' => $entry])
            @endforeach
        </div>
    @endif
</x-layouts.member>
