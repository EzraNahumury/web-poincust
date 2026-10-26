<x-layouts.admin title="Tambah Admin" heading="Tambah Admin">
    <div class="mx-auto max-w-xl">
        <div class="card">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                @csrf
                <x-input name="name" label="Nama lengkap" autofocus />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="username" label="Username" />
                    <x-input name="email" type="email" label="Email" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="password" type="password" label="Password" autocomplete="new-password" hint="Min. 10 karakter, huruf & angka." />
                    <x-input name="password_confirmation" type="password" label="Konfirmasi password" autocomplete="new-password" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="label">Role</label>
                        <select name="role_id" class="input">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->label ?? $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select name="status" class="input">
                            <option value="active" @selected(old('status', 'active') === 'active')>Aktif</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
