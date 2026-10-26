<x-layouts.admin title="Customer" heading="Data Customer">
    @can(\App\Domain\Auth\Permissions::CUSTOMERS_MANAGE)
        <x-slot:actions>
            <a href="{{ route('admin.customers.create') }}" class="btn-primary btn-sm">+ Tambah Customer</a>
        </x-slot:actions>
    @endcan

    <div class="card mb-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[220px] flex-1">
                <label class="label">Cari</label>
                <input name="q" value="{{ $search }}" placeholder="Nama, username, no. member, WhatsApp" class="input">
            </div>
            <div>
                <label class="label">Status</label>
                <select name="status" class="input">
                    <option value="">Semua</option>
                    <option value="active" @selected($status === 'active')>Aktif</option>
                    <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <button class="btn-dark" type="submit">Cari</button>
            @if ($search || $status)
                <a href="{{ route('admin.customers.index') }}" class="btn-ghost">Reset</a>
            @endif
        </form>
    </div>

    <div class="card-flush overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Customer</th><th>WhatsApp</th><th class="text-right">Saldo Poin</th><th class="text-right">Transaksi</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <p class="font-medium text-slate-900">{{ $customer->name }}</p>
                                <p class="text-xs text-slate-500">{{ $customer->member_number }} · {{ '@'.$customer->username }}</p>
                            </td>
                            <td class="text-slate-600">{{ $customer->maskedWhatsapp() }}</td>
                            <td class="text-right font-semibold">{{ number_format($customer->points_balance, 0, ',', '.') }}</td>
                            <td class="text-right text-slate-600">{{ $customer->transactions_count }}</td>
                            <td><x-status-badge :status="$customer->status" /></td>
                            <td class="text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-slate-400">Tidak ada customer yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">{{ $customers->links() }}</div>
</x-layouts.admin>
