<x-layouts.admin title="Pengaturan Program Poin" heading="Pengaturan Program Poin">
    <div class="mx-auto max-w-xl">
        <div class="card">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <x-input name="rupiah_per_point" type="number" min="1" label="Rupiah per 1 poin" :value="$rupiahPerPoint"
                         hint="Mis. 10000 artinya setiap belanja Rp10.000 (memenuhi syarat) = 1 poin." />
                <x-input name="redeem_value" type="number" min="1" label="Nilai tukar per poin (Rp)" :value="$redeemValue"
                         hint="Mis. 500 artinya 1 poin = diskon Rp500." />

                <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                    Perubahan berlaku untuk transaksi <strong>baru</strong>. Transaksi lama tetap memakai nilai saat dicatat (tersimpan di masing-masing transaksi dan ledger).
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
