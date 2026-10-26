<x-layouts.member title="Dashboard">
    <div class="space-y-6">
        <div>
            <p class="text-sm text-slate-500">Halo,</p>
            <h1 class="text-2xl font-bold text-slate-900">{{ $customer->name }} 👋</h1>
        </div>

        {{-- Premium points card --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-ink-900 via-ink-900 to-brand-700 p-6 text-white shadow-xl">
            <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-sm text-white/70">Saldo poin kamu</p>
                    <p class="mt-1 text-5xl font-extrabold tracking-tight">{{ number_format($balance, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-white/70">poin</p>
                </div>
                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-medium">{{ $customer->member_number }}</span>
            </div>
            <div class="relative mt-6 flex items-center justify-between border-t border-white/15 pt-4">
                <div>
                    <p class="text-xs text-white/70">Estimasi nilai diskon</p>
                    <p class="text-xl font-bold">{{ rupiah($estimatedDiscount) }}</p>
                </div>
                <p class="max-w-[12rem] text-right text-[11px] leading-snug text-white/60">
                    1 poin = {{ rupiah($redeemValue) }}. Poin hanya untuk diskon, tidak dapat diuangkan.
                </p>
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="grid grid-cols-2 gap-4">
            <x-stat label="Total transaksi" :value="$transactionCount . 'x'" accent="ink" />
            <x-stat label="Transaksi terakhir"
                    :value="$lastTransaction ? rupiah($lastTransaction->final_amount) : '—'"
                    :sub="$lastTransaction ? jakarta_date($lastTransaction->transaction_date)->translatedFormat('d M Y') : 'Belum ada'" />
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Recent point movements --}}
            <div class="card-flush overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4">
                    <h2 class="font-semibold text-slate-900">Mutasi poin terbaru</h2>
                    <a href="{{ route('member.points') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua</a>
                </div>
                @forelse ($recentLedger as $entry)
                    @include('member.partials.ledger-row', ['entry' => $entry])
                @empty
                    <div class="px-5 pb-6"><x-empty title="Belum ada mutasi poin" message="Mutasi akan muncul setelah transaksi pertamamu." /></div>
                @endforelse
            </div>

            {{-- Recent transactions --}}
            <div class="card-flush overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4">
                    <h2 class="font-semibold text-slate-900">Transaksi terbaru</h2>
                    <a href="{{ route('member.transactions') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lihat semua</a>
                </div>
                @forelse ($recentTransactions as $txn)
                    <a href="{{ route('member.transactions.show', $txn) }}" class="flex items-center justify-between border-t border-slate-100 px-5 py-3 hover:bg-slate-50">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $txn->transaction_number }}</p>
                            <p class="text-xs text-slate-500">{{ jakarta_date($txn->transaction_date)->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-900">{{ rupiah($txn->final_amount) }}</p>
                            <p class="text-xs {{ $txn->isReversed() ? 'text-red-500' : 'text-emerald-600' }}">
                                {{ $txn->isReversed() ? 'Dibatalkan' : '+' . points_label($txn->points_earned) }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="px-5 pb-6"><x-empty title="Belum ada transaksi" message="Belanja di Avasport untuk mulai mengumpulkan poin." /></div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.member>
