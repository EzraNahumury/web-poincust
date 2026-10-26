<x-layouts.member title="Keamanan">
    <h1 class="mb-5 text-2xl font-bold text-slate-900">Keamanan akun</h1>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Change password --}}
        <div class="card">
            <h2 class="font-semibold text-slate-900">Ubah password</h2>
            <p class="mt-1 text-sm text-slate-500">Password digunakan untuk masuk ke akun member.</p>
            <form method="POST" action="{{ route('member.security.password') }}" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <x-input name="current_password" type="password" label="Password saat ini" autocomplete="current-password" />
                <x-input name="password" type="password" label="Password baru" autocomplete="new-password" hint="Min. 8 karakter, kombinasi huruf & angka." />
                <x-input name="password_confirmation" type="password" label="Konfirmasi password baru" autocomplete="new-password" />
                <div class="flex justify-end"><button type="submit" class="btn-primary">Ubah password</button></div>
            </form>
        </div>

        {{-- Change PIN --}}
        <div class="card">
            <h2 class="font-semibold text-slate-900">Ubah PIN member</h2>
            <p class="mt-1 text-sm text-slate-500">PIN 6 digit untuk verifikasi tambahan. Jangan bagikan ke siapa pun.</p>
            <form method="POST" action="{{ route('member.security.pin') }}" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <x-input name="current_pin" type="password" label="PIN saat ini" inputmode="numeric" maxlength="6" />
                <x-input name="pin" type="password" label="PIN baru (6 digit)" inputmode="numeric" maxlength="6" />
                <x-input name="pin_confirmation" type="password" label="Konfirmasi PIN baru" inputmode="numeric" maxlength="6" />
                <div class="flex justify-end"><button type="submit" class="btn-primary">Ubah PIN</button></div>
            </form>
        </div>
    </div>
</x-layouts.member>
