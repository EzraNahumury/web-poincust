<x-layouts.member title="Riwayat Poin">
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Riwayat mutasi poin</h1>
        <div class="rounded-xl bg-ink-950 px-4 py-2 text-white">
            <span class="text-xs text-white/70">Saldo</span>
            <span class="ml-2 text-lg font-bold">{{ number_format($balance, 0, ',', '.') }}</span>
            <span class="text-sm text-white/70">poin · ≈ {{ rupiah($estimatedDiscount) }}</span>
        </div>
    </div>

    @if ($entries->isEmpty())
        <x-empty title="Belum ada mutasi poin" message="Setiap perubahan poin akan tercatat di sini." />
    @else
        <div class="card-flush overflow-hidden">
            @foreach ($entries as $entry)
                @include('member.partials.ledger-row', ['entry' => $entry])
            @endforeach
        </div>
        <div class="mt-5">{{ $entries->links() }}</div>
    @endif
</x-layouts.member>
