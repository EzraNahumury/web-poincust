@php
    $labels = [
        'EARN' => 'Perolehan poin',
        'REDEEM' => 'Penukaran poin',
        'REVERSAL' => 'Pembalikan transaksi',
        'ADJUSTMENT_CREDIT' => 'Penyesuaian poin (+)',
        'ADJUSTMENT_DEBIT' => 'Penyesuaian poin (−)',
        'EXPIRED' => 'Poin kedaluwarsa',
    ];
    $positive = $entry->points_delta >= 0;
@endphp

<div class="flex items-center justify-between gap-3 border-t border-slate-100 px-5 py-3">
    <div class="flex min-w-0 items-center gap-3">
        <span class="flex h-9 w-9 flex-none items-center justify-center rounded-full {{ $positive ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
            @if ($positive)
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            @else
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
            @endif
        </span>
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-slate-800">{{ $labels[$entry->type] ?? $entry->type }}</p>
            <p class="truncate text-xs text-slate-500">{{ $entry->description ?? jakarta_date($entry->created_at)->translatedFormat('d M Y, H:i') }}</p>
        </div>
    </div>
    <div class="text-right">
        <p class="text-sm font-semibold {{ $positive ? 'text-emerald-600' : 'text-red-600' }}">
            {{ $positive ? '+' : '' }}{{ number_format($entry->points_delta, 0, ',', '.') }}
        </p>
        <p class="text-xs text-slate-400">saldo {{ number_format($entry->balance_after, 0, ',', '.') }}</p>
    </div>
</div>
