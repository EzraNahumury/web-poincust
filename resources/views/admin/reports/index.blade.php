<x-layouts.admin title="Laporan" heading="Laporan">
    <div class="card mb-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div><label class="label">Dari</label><input type="date" name="from" value="{{ $from }}" class="input"></div>
            <div><label class="label">Sampai</label><input type="date" name="to" value="{{ $to }}" class="input"></div>
            <button class="btn-dark" type="submit">Terapkan</button>
        </form>
        <p class="mt-3 text-xs text-slate-500">Periode: {{ jakarta_date($from)->translatedFormat('d M Y') }} – {{ jakarta_date($to)->translatedFormat('d M Y') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-stat label="Transaksi selesai" :value="number_format($transactionCount, 0, ',', '.')" accent="ink" />
        <x-stat label="Nilai transaksi (gross)" :value="rupiah($grossSum)" />
        <x-stat label="Total dibayar" :value="rupiah($finalSum)" accent="brand" />
        <x-stat label="Poin diberikan" :value="points_label($pointsEarned)" accent="green" />
        <x-stat label="Poin ditukar" :value="points_label($pointsRedeemed)" accent="red" />
        <x-stat label="Pembatalan" :value="$reversalCount . 'x'" accent="red" />
    </div>

    <div class="card mt-6">
        <h3 class="font-semibold text-slate-900">Ekspor data (CSV)</h3>
        <p class="mt-1 text-sm text-slate-500">Ekspor menghormati periode yang dipilih. Data rahasia (password/PIN) tidak pernah diekspor.</p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.export', ['type' => 'transactions', 'from' => $from, 'to' => $to]) }}" class="btn-secondary">Transaksi (periode)</a>
            <a href="{{ route('admin.reports.export', ['type' => 'points', 'from' => $from, 'to' => $to]) }}" class="btn-secondary">Mutasi Poin (periode)</a>
            <a href="{{ route('admin.reports.export', ['type' => 'customers']) }}" class="btn-secondary">Semua Customer</a>
        </div>
    </div>
</x-layouts.admin>
