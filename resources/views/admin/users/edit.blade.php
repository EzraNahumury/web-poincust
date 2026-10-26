<x-layouts.admin title="Edit Admin" heading="Edit Admin">
    <div class="mx-auto max-w-xl">
        <div class="card">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <x-input name="name" label="Nama lengkap" :value="$user->name" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="username" label="Username" :value="$user->username" />
                    <x-input name="email" type="email" label="Email" :value="$user->email" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="password" type="password" label="Password baru (opsional)" autocomplete="new-password" hint="Kosongkan jika tidak diubah." />
                    <x-input name="password_confirmation" type="password" label="Konfirmasi password baru" autocomplete="new-password" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="label">Role</label>
                        <select name="role_id" class="input" @disabled($user->id === auth('web')->id())>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ $role->label ?? $role->name }}</option>
                            @endforeach
                        </select>
                        @if ($user->id === auth('web')->id())
                            <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                            <p class="form-hint">Anda tidak dapat mengubah role akun sendiri.</p>
                        @endif
                        @error('role_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select name="status" class="input">
                            <option value="active" @selected(old('status', $user->status) === 'active')>Aktif</option>
                            <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
