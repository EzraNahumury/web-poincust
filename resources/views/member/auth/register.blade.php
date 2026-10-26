<x-layouts.guest title="Daftar Member Avasport">
    <h2 class="text-2xl font-bold text-slate-900">Daftar member baru</h2>
    <p class="mt-1 text-sm text-slate-500">Sudah punya akun?
        <a href="{{ route('member.login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Masuk di sini</a>
    </p>

    <form method="POST" action="{{ route('member.register.store') }}" class="mt-8 space-y-5">
        @csrf
        <x-input name="name" label="Nama lengkap" autocomplete="name" autofocus />

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-input name="birth_date" type="date" label="Tanggal lahir" />
            <x-input name="whatsapp_number" type="tel" label="Nomor WhatsApp" placeholder="08xxxxxxxxxx" inputmode="numeric" />
        </div>

        <x-input name="username" label="Username" hint="Huruf kecil, angka, titik, _ atau - (min. 3 karakter)" autocomplete="username" />
        <x-input name="email" type="email" label="Email (opsional)" autocomplete="email" />

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-input name="password" type="password" label="Password" autocomplete="new-password" />
            <x-input name="password_confirmation" type="password" label="Konfirmasi password" autocomplete="new-password" />
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-input name="pin" type="password" label="PIN member (6 digit)" inputmode="numeric" maxlength="6" hint="Rahasia. Jangan bagikan ke siapa pun." />
            <x-input name="pin_confirmation" type="password" label="Konfirmasi PIN" inputmode="numeric" maxlength="6" />
        </div>

        <label class="flex items-start gap-2 text-sm text-slate-600">
            <input type="checkbox" name="terms" value="1" class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            <span>Saya menyetujui syarat membership dan kebijakan privasi Avasport.</span>
        </label>
        @error('terms')<p class="form-error">{{ $message }}</p>@enderror

        <button type="submit" class="btn-primary w-full">Buat Akun Member</button>
    </form>
</x-layouts.guest>
