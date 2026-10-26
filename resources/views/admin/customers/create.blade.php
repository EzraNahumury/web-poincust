<x-layouts.admin title="Tambah Customer" heading="Tambah Customer">
    <div class="mx-auto max-w-2xl">
        <div class="card">
            <form method="POST" action="{{ route('admin.customers.store') }}" class="space-y-5">
                @csrf
                <x-input name="name" label="Nama lengkap" autofocus />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="birth_date" type="date" label="Tanggal lahir" />
                    <x-input name="whatsapp_number" type="tel" label="Nomor WhatsApp" placeholder="08xxxxxxxxxx" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="username" label="Username" hint="huruf kecil/angka/._-" />
                    <x-input name="email" type="email" label="Email (opsional)" />
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    PIN awal diisi bersama customer. Sarankan customer segera menggantinya sendiri lewat menu Keamanan. PIN tidak pernah dapat dilihat admin setelah disimpan.
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="password" type="password" label="Password awal" autocomplete="new-password" />
                    <x-input name="password_confirmation" type="password" label="Konfirmasi password" autocomplete="new-password" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="pin" type="password" label="PIN (6 digit)" inputmode="numeric" maxlength="6" />
                    <x-input name="pin_confirmation" type="password" label="Konfirmasi PIN" inputmode="numeric" maxlength="6" />
                </div>

                <div>
                    <label class="label">Status</label>
                    <select name="status" class="input">
                        <option value="active" @selected(old('status', 'active') === 'active')>Aktif</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Nonaktif</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.customers.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan Customer</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
