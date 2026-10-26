@php use App\Domain\Auth\Permissions; @endphp
<x-layouts.admin title="Detail Customer" :heading="$customer->name">
    <x-slot:actions>
        @can(Permissions::TRANSACTIONS_CREATE)
            <a href="{{ route('admin.transactions.create', ['customer' => $customer->id]) }}" class="btn-primary btn-sm">+ Transaksi</a>
        @endcan
        @can(Permissions::CUSTOMERS_MANAGE)
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn-secondary btn-sm">Edit</a>
            <form method="POST" action="{{ route('admin.customers.status', $customer) }}" onsubmit="return confirm('Ubah status customer ini?')">
                @csrf @method('PATCH')
                <button class="btn-secondary btn-sm" type="submit">{{ $customer->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
            </form>
        @endcan
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Profile + balance --}}
        <div class="space-y-6 lg:col-span-1">
            <div class="card">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-ink-900 text-xl font-bold text-white">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</div>
                    <div>
                        <p class="font-semibold text-slate-900">{{ $customer->name }}</p>
                        <p class="text-sm text-slate-500">{{ $customer->member_number }}</p>
                        <x-status-badge :status="$customer->status" />
                    </div>
                </div>
                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Username</dt><dd class="font-medium">{{ '@'.$customer->username }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">WhatsApp</dt><dd class="font-medium">{{ $customer->whatsapp_number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Tgl lahir</dt><dd class="font-medium">{{ jakarta_date($customer->birth_date)->translatedFormat('d M Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd class="font-medium">{{ $customer->email ?: '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Daftar</dt><dd class="font-medium">{{ jakarta_date($customer->created_at)->translatedFormat('d M Y') }}</dd></div>
                </dl>
            </div>

            <div class="card bg-ink-950 text-white ring-ink-900">
                <p class="text-sm text-white/70">Saldo poin</p>
                <p class="text-3xl font-extrabold">{{ number_format($customer->points_balance, 0, ',', '.') }}</p>
                @if ($customer->points_balance < 0)
                    <p class="mt-2 rounded-lg bg-red-500/20 px-2 py-1 text-xs text-red-200">Saldo negatif — perlu peninjauan.</p>
                @endif
                <p class="mt-1 text-xs text-white/60">{{ $customer->transactions_count }} transaksi tercatat</p>
            </div>

            @can(Permissions::POINTS_ADJUST)
                <div class="card">
                    <h3 class="font-semibold text-slate-900">Penyesuaian poin</h3>
                    <p class="mt-1 text-xs text-slate-500">Tercatat di ledger & audit log. Wajib menyertakan alasan.</p>
                    <form method="POST" action="{{ route('admin.points.adjust', $customer) }}" class="mt-4 space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="label">Tipe</label>
                                <select name="type" class="input">
                                    <option value="credit">Tambah (+)</option>
                                    <option value="debit">Kurangi (−)</option>
                                </select>
                            </div>
                            <x-input name="points" type="number" min="1" label="Jumlah poin" />
                        </div>
                        <x-input name="reason" label="Alasan" />
                        <button type="submit" class="btn-dark w-full" onclick="return confirm('Simpan penyesuaian poin ini?')">Simpan Penyesuaian</button>
                    </form>
                </div>
            @endcan
        </div>

        {{-- Transactions + ledger --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="card-flush overflow-hidden">
                <h3 class="px-5 py-4 font-semibold text-slate-900">Transaksi terakhir</h3>
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>No</th><th>Tanggal</th><th class="text-right">Total</th><th class="text-right">Poin</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($transactions as $txn)
                                <tr>
                                    <td class="font-medium">{{ $txn->transaction_number }}</td>
                                    <td class="text-xs text-slate-500">{{ jakarta_date($txn->transaction_date)->translatedFormat('d M Y') }}</td>
                                    <td class="text-right">{{ rupiah($txn->final_amount) }}</td>
                                    <td class="text-right text-xs"><span class="text-emerald-600">+{{ $txn->points_earned }}</span>@if ($txn->points_redeemed)<span class="text-red-500"> / −{{ $txn->points_redeemed }}</span>@endif</td>
                                    <td><x-status-badge :status="$txn->status" /></td>
                                    <td class="text-right"><a href="{{ route('admin.transactions.show', $txn) }}" class="text-sm font-medium text-brand-600">Lihat</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-slate-400">Belum ada transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-flush overflow-hidden">
                <h3 class="px-5 py-4 font-semibold text-slate-900">Mutasi poin terakhir</h3>
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>Waktu</th><th>Tipe</th><th>Keterangan</th><th class="text-right">Perubahan</th><th class="text-right">Saldo</th></tr></thead>
                        <tbody>
                            @forelse ($ledger as $l)
                                <tr>
                                    <td class="text-xs text-slate-500">{{ jakarta_date($l->created_at)->translatedFormat('d M, H:i') }}</td>
                                    <td class="text-xs"><span class="badge-slate">{{ $l->type }}</span></td>
                                    <td class="max-w-[16rem] truncate text-slate-600">{{ $l->description }}</td>
                                    <td class="text-right font-semibold {{ $l->points_delta >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $l->points_delta >= 0 ? '+' : '' }}{{ $l->points_delta }}</td>
                                    <td class="text-right text-slate-500">{{ $l->balance_after }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada mutasi poin.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
