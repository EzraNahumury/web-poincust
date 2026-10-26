<x-layouts.member title="Profil">
    <h1 class="mb-5 text-2xl font-bold text-slate-900">Profil saya</h1>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-1">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-xl font-bold text-white">
                    {{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-slate-900">{{ $customer->name }}</p>
                    <p class="text-sm text-slate-500">{{ $customer->member_number }}</p>
                </div>
            </div>
            <dl class="mt-5 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Username</dt><dd class="font-medium text-slate-800">{{ $customer->username }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Bergabung</dt><dd class="font-medium text-slate-800">{{ jakarta_date($customer->created_at)->translatedFormat('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd><x-status-badge :status="$customer->status" /></dd></div>
            </dl>
            <a href="{{ route('member.security') }}" class="btn-secondary mt-5 w-full">Keamanan akun (password & PIN)</a>
        </div>

        <div class="card lg:col-span-2">
            <h2 class="font-semibold text-slate-900">Ubah data profil</h2>
            <form method="POST" action="{{ route('member.profile.update') }}" class="mt-5 space-y-5">
                @csrf
                @method('PUT')
                <x-input name="name" label="Nama lengkap" :value="$customer->name" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="whatsapp_number" type="tel" label="Nomor WhatsApp" :value="$customer->whatsapp_number" inputmode="numeric" />
                    <x-input name="email" type="email" label="Email (opsional)" :value="$customer->email" />
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Simpan perubahan</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.member>
