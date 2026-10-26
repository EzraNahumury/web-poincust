<x-layouts.guest title="Masuk Member Avasport">
    <h2 class="text-2xl font-bold text-slate-900">Masuk ke akun member</h2>
    <p class="mt-1 text-sm text-slate-500">Belum punya akun?
        <a href="{{ route('member.register') }}" class="font-semibold text-brand-600 hover:text-brand-700">Daftar di sini</a>
    </p>

    <form method="POST" action="{{ route('member.login.store') }}" class="mt-8 space-y-5">
        @csrf
        <x-input name="username" label="Username" autocomplete="username" autofocus inputmode="text" />
        <x-input name="password" type="password" label="Password" autocomplete="current-password" />

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Ingat saya
            </label>
            <a href="{{ route('member.forgot') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Lupa password?</a>
        </div>

        <button type="submit" class="btn-primary w-full">Masuk</button>
    </form>
</x-layouts.guest>
