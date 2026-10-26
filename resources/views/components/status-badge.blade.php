@props(['status'])

@php
    $map = [
        'active' => ['badge-green', 'Aktif'],
        'inactive' => ['badge-slate', 'Nonaktif'],
        'completed' => ['badge-green', 'Selesai'],
        'reversed' => ['badge-red', 'Dibatalkan'],
        'pending' => ['badge-amber', 'Belum dihubungi'],
        'contacted' => ['badge-green', 'Sudah dihubungi'],
    ];
    [$class, $text] = $map[$status] ?? ['badge-slate', ucfirst((string) $status)];
@endphp

<span class="{{ $class }}">{{ $text }}</span>
