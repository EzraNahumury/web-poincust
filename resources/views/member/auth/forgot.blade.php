<x-layouts.guest title="Lupa Password">
    <h2 class="text-2xl font-bold text-slate-900">Lupa password?</h2>
    <p class="mt-3 text-slate-600">
        Demi keamanan akun dan saldo poin Anda, pemulihan password member dilakukan
        <strong>langsung oleh admin Avasport</strong> di toko dengan verifikasi identitas.
    </p>

    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
        <p class="font-semibold text-slate-800">Langkah pemulihan:</p>
        <ol class="mt-2 list-inside list-decimal space-y-1">
            <li>Kunjungi kasir / admin Avasport.</li>
            <li>Tunjukkan identitas dan sebutkan nomor member Anda.</li>
            <li>Admin akan membantu mereset akses akun Anda secara aman.</li>
        </ol>
    </div>

    <p class="mt-6 text-sm text-slate-500">
        PIN member tidak pernah dapat dilihat admin dan tidak dikirim melalui WhatsApp.
    </p>

    <a href="{{ route('member.login') }}" class="btn-secondary mt-8 w-full">Kembali ke halaman masuk</a>
</x-layouts.guest>
