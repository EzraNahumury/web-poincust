<x-layouts.admin title="Tambah Transaksi" heading="Tambah Transaksi">
    <div class="mx-auto grid max-w-4xl gap-6 lg:grid-cols-5">
        <form method="POST" action="{{ route('admin.transactions.store') }}" class="card space-y-5 lg:col-span-3" id="txn-form">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
            <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id', $preselect?->id) }}">

            {{-- Customer picker --}}
            <div>
                <label class="label">Customer</label>
                <div id="cust-selected" class="{{ $preselect ? '' : 'hidden' }} mb-2 flex items-center justify-between rounded-xl bg-slate-100 px-4 py-3">
                    <div>
                        <p class="font-semibold text-slate-900" id="cust-name">{{ $preselect?->name }}</p>
                        <p class="text-xs text-slate-500"><span id="cust-member">{{ $preselect?->member_number }}</span> · saldo <span id="cust-balance" class="font-semibold">{{ $preselect?->points_balance ?? 0 }}</span> poin</p>
                    </div>
                    <button type="button" class="btn-ghost btn-sm" onclick="clearCustomer()">Ganti</button>
                </div>
                <div id="cust-picker" class="{{ $preselect ? 'hidden' : '' }} relative">
                    <input type="text" id="cust-search" placeholder="Cari nama / nomor member / WhatsApp" class="input" autocomplete="off">
                    <div id="cust-results" class="absolute z-10 mt-1 hidden max-h-64 w-full overflow-auto rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                </div>
                @error('customer_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-input name="gross_amount" id="gross_amount" type="number" min="0" label="Total belanja (Rp)" inputmode="numeric" />
                <x-input name="transaction_discount" id="transaction_discount" type="number" min="0" label="Diskon transaksi (Rp)" value="0" inputmode="numeric" />
            </div>

            <x-input name="points_redeemed" id="points_redeemed" type="number" min="0" label="Poin ditukar" value="0" inputmode="numeric" hint="Kosongkan/0 jika tidak menukar poin." />

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-input name="transaction_date" type="datetime-local" label="Waktu transaksi (opsional)" />
                <x-input name="external_receipt_no" label="No. struk eksternal (opsional)" />
            </div>

            <div>
                <label class="label">Catatan (opsional)</label>
                <textarea name="notes" rows="2" class="input">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.transactions.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Transaksi</button>
            </div>
        </form>

        {{-- Live preview --}}
        <div class="lg:col-span-2">
            <div class="card sticky top-20">
                <h3 class="font-semibold text-slate-900">Rincian (estimasi)</h3>
                <p class="mt-1 text-xs text-slate-500">Perkiraan di layar. Nilai final selalu dihitung ulang oleh server saat disimpan.</p>
                <dl class="mt-4 space-y-2.5 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Belanja memenuhi syarat</dt><dd class="font-medium" id="pv-eligible">Rp0</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Diskon dari poin</dt><dd class="font-medium text-red-600" id="pv-redeem">− Rp0</dd></div>
                    <div class="flex justify-between border-t border-slate-200 pt-2.5 text-base"><dt class="font-semibold text-slate-700">Total bayar</dt><dd class="font-bold text-slate-900" id="pv-final">Rp0</dd></div>
                    <div class="mt-2 flex justify-between rounded-lg bg-emerald-50 px-3 py-2"><dt class="text-emerald-700">Poin diperoleh</dt><dd class="font-bold text-emerald-700" id="pv-earned">0</dd></div>
                    <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><dt class="text-slate-500">Estimasi saldo akhir</dt><dd class="font-semibold" id="pv-balance">0</dd></div>
                </dl>
            </div>
        </div>
    </div>

    @push('scripts')
    @endpush
    <script>
        const csrf = document.querySelector('meta[name=csrf-token]').content;
        const rupiah = n => 'Rp' + (n || 0).toLocaleString('id-ID');
        const $ = id => document.getElementById(id);
        let t;

        function debounce(fn, ms) { return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

        // --- Customer search ---
        const searchBox = $('cust-search'), results = $('cust-results');
        if (searchBox) {
            searchBox.addEventListener('input', debounce(async () => {
                const q = searchBox.value.trim();
                if (q.length < 1) { results.classList.add('hidden'); return; }
                const res = await fetch(`{{ route('admin.customers.search') }}?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                results.innerHTML = data.length ? data.map(c =>
                    `<button type="button" class="block w-full px-4 py-2.5 text-left hover:bg-slate-50" onclick='pickCustomer(${JSON.stringify(c)})'>
                        <span class="block text-sm font-medium text-slate-800">${c.name}</span>
                        <span class="block text-xs text-slate-500">${c.member_number} · saldo ${c.points_balance} poin</span>
                    </button>`).join('') : '<p class="px-4 py-3 text-sm text-slate-400">Tidak ditemukan.</p>';
                results.classList.remove('hidden');
            }, 250));
        }

        function pickCustomer(c) {
            $('customer_id').value = c.id;
            $('cust-name').textContent = c.name;
            $('cust-member').textContent = c.member_number;
            $('cust-balance').textContent = c.points_balance;
            $('cust-selected').classList.remove('hidden');
            $('cust-picker').classList.add('hidden');
            results.classList.add('hidden');
            preview();
        }

        function clearCustomer() {
            $('customer_id').value = '';
            $('cust-selected').classList.add('hidden');
            $('cust-picker').classList.remove('hidden');
            $('cust-search').value = '';
            preview();
        }

        // --- Live preview ---
        async function preview() {
            const payload = {
                customer_id: $('customer_id').value || null,
                gross_amount: parseInt($('gross_amount').value || '0', 10),
                transaction_discount: parseInt($('transaction_discount').value || '0', 10),
                points_redeemed: parseInt($('points_redeemed').value || '0', 10),
            };
            try {
                const res = await fetch(`{{ route('admin.transactions.preview') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload),
                });
                if (!res.ok) return;
                const d = await res.json();
                $('pv-eligible').textContent = rupiah(d.eligible_amount);
                $('pv-redeem').textContent = '− ' + rupiah(d.redemption_discount);
                $('pv-final').textContent = rupiah(d.final_amount);
                $('pv-earned').textContent = d.points_earned + ' poin';
                $('pv-balance').textContent = d.balance_after + ' poin';
            } catch (e) { /* preview is best-effort only */ }
        }

        ['gross_amount', 'transaction_discount', 'points_redeemed'].forEach(id => {
            const el = $(id);
            if (el) el.addEventListener('input', debounce(preview, 300));
        });
        document.addEventListener('DOMContentLoaded', preview);
    </script>
</x-layouts.admin>
