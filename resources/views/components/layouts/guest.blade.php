@props(['title' => null])
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Poin Member Avasport' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full">
    <div class="flex min-h-screen flex-col lg:flex-row">
        {{-- Brand panel --}}
        <div class="relative hidden overflow-hidden bg-ink-950 lg:flex lg:w-1/2 lg:flex-col lg:justify-between lg:p-12">
            <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-600/30 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-ink-500/30 blur-3xl"></div>
            <x-brand tone="light" size="lg" class="relative" />
            <div class="relative">
                <h1 class="text-4xl font-extrabold leading-tight text-white">
                    Satu poin lebih dekat<br>ke hadiah berikutnya.
                </h1>
                <p class="mt-4 max-w-md text-ink-200">
                    Kumpulkan poin dari setiap belanja di Avasport, pantau saldo kapan saja,
                    dan tukarkan jadi diskon.
                </p>
            </div>
            <p class="relative text-sm text-ink-300">&copy; {{ date('Y') }} Avasport. Program membership.</p>
        </div>

        {{-- Form panel --}}
        <div class="flex flex-1 flex-col justify-center px-6 py-10 sm:px-12">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <x-brand size="md" />
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
