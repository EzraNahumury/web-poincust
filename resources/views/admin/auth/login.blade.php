<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Admin · Avasport</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-ink-950 px-6 py-12">
    <div class="w-full max-w-sm">
        <div class="mb-8 flex flex-col items-center text-center">
            <x-brand tone="light" size="lg" />
            <p class="mt-3 text-sm text-ink-300">Panel Administrasi &amp; Kasir</p>
        </div>

        <div class="card">
            <h1 class="text-xl font-bold text-slate-900">Masuk</h1>
            <p class="mt-1 text-sm text-slate-500">Gunakan akun admin yang diberikan.</p>

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-5">
                @csrf
                <x-input name="login" label="Username atau email" autocomplete="username" autofocus />
                <x-input name="password" type="password" label="Password" autocomplete="current-password" />
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Ingat saya
                </label>
                <button type="submit" class="btn-primary w-full">Masuk</button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-ink-400">Akses terbatas. Aktivitas dicatat dalam audit log.</p>
    </div>
</body>
</html>
