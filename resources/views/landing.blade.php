<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Poin Member Avasport — Belanja, Kumpulkan Poin, Hemat</title>
    <meta name="description" content="Program membership Avasport: kumpulkan poin dari setiap belanja dan tukar jadi diskon.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-white">
    <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
        <x-brand size="md" />
        <div class="flex items-center gap-2">
            <a href="{{ route('member.login') }}" class="btn-ghost btn-sm">Masuk</a>
            <a href="{{ route('member.register') }}" class="btn-primary btn-sm">Daftar</a>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-ink-950">
        <div class="absolute -right-32 -top-24 h-96 w-96 rounded-full bg-brand-600/30 blur-3xl"></div>
        <div class="absolute -bottom-32 left-0 h-96 w-96 rounded-full bg-ink-500/20 blur-3xl"></div>
        <div class="relative mx-auto grid max-w-6xl items-center gap-10 px-6 py-20 lg:grid-cols-2">
            <div>
                <span class="badge-brand">Program Membership</span>
                <h1 class="mt-4 text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                    Setiap belanja,<br>jadi lebih berharga.
                </h1>
                <p class="mt-5 max-w-lg text-lg text-ink-200">
                    Jadi member Avasport, kumpulkan poin otomatis dari transaksi di toko,
                    dan tukarkan poinmu menjadi diskon belanja berikutnya.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('member.register') }}" class="btn-primary">Daftar Member Gratis</a>
                    <a href="{{ route('member.login') }}" class="btn-dark">Cek Saldo Poin</a>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-sm">
                <div class="rounded-3xl bg-gradient-to-br from-brand-500 to-brand-700 p-6 text-white shadow-2xl">
                    <p class="text-sm font-medium text-white/80">Kartu Member</p>
                    <p class="mt-6 text-sm text-white/70">Saldo Poin</p>
                    <p class="text-4xl font-extrabold">1.250 <span class="text-xl font-semibold">poin</span></p>
                    <p class="mt-1 text-sm text-white/80">≈ {{ rupiah(1250 * $redeemValue) }} nilai diskon</p>
                    <div class="mt-8 flex items-center justify-between text-sm">
                        <span class="font-mono tracking-widest">AVA•••••</span>
                        <span>Avasport</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="mx-auto max-w-6xl px-6 py-20">
        <h2 class="text-center text-3xl font-bold text-slate-900">Cara kerjanya</h2>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ([
                ['1', 'Daftar member', 'Buat akun member sekali saja dengan nama, nomor WhatsApp, dan PIN.'],
                ['2', 'Belanja & dapat poin', 'Kasir mencatat transaksimu. Poin dihitung otomatis dan masuk ke saldomu.'],
                ['3', 'Tukar jadi diskon', 'Gunakan poin sebagai potongan belanja saat transaksi berikutnya.'],
            ] as [$n, $t, $d])
                <div class="card">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-lg font-bold text-white">{{ $n }}</div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $t }}</h3>
                    <p class="mt-2 text-slate-600">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Rules --}}
    <section class="bg-slate-50">
        <div class="mx-auto grid max-w-6xl gap-6 px-6 py-16 sm:grid-cols-2">
            <div class="card flex items-center gap-5">
                <div class="text-4xl">🛍️</div>
                <div>
                    <p class="text-sm text-slate-500">Perolehan poin</p>
                    <p class="text-2xl font-bold text-slate-900">{{ rupiah($rupiahPerPoint) }} = 1 poin</p>
                    <p class="mt-1 text-sm text-slate-500">Dihitung dari belanja yang memenuhi syarat, dibulatkan ke bawah.</p>
                </div>
            </div>
            <div class="card flex items-center gap-5">
                <div class="text-4xl">🎁</div>
                <div>
                    <p class="text-sm text-slate-500">Nilai tukar</p>
                    <p class="text-2xl font-bold text-slate-900">1 poin = {{ rupiah($redeemValue) }}</p>
                    <p class="mt-1 text-sm text-slate-500">Tukarkan poin sebagai diskon, tidak dapat diuangkan.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="border-t border-slate-200">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 py-8 sm:flex-row">
            <x-brand size="sm" />
            <p class="text-sm text-slate-500">&copy; {{ date('Y') }} Avasport. Semua hak cipta dilindungi.</p>
        </div>
    </footer>
</body>
</html>
