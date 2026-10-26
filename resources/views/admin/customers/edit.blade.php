<x-layouts.admin title="Edit Customer" heading="Edit Customer">
    <div class="mx-auto max-w-2xl">
        <div class="card">
            <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <x-input name="name" label="Nama lengkap" :value="$customer->name" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="birth_date" type="date" label="Tanggal lahir" :value="$customer->birth_date->toDateString()" />
                    <x-input name="whatsapp_number" type="tel" label="Nomor WhatsApp" :value="$customer->whatsapp_number" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="username" label="Username" :value="$customer->username" />
                    <x-input name="email" type="email" label="Email (opsional)" :value="$customer->email" />
                </div>
                <div>
                    <label class="label">Status</label>
                    <select name="status" class="input">
                        <option value="active" @selected(old('status', $customer->status) === 'active')>Aktif</option>
                        <option value="inactive" @selected(old('status', $customer->status) === 'inactive')>Nonaktif</option>
                    </select>
                </div>

                <p class="form-hint">Password dan PIN tidak dapat diubah dari sini. Password/PIN hanya dapat diganti oleh customer (atau direset lewat prosedur bantuan admin).</p>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
