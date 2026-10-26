@props(['label', 'value', 'sub' => null, 'accent' => 'slate'])

@php
    $ring = [
        'brand' => 'text-brand-600',
        'green' => 'text-emerald-600',
        'red' => 'text-red-600',
        'ink' => 'text-ink-900',
        'slate' => 'text-slate-900',
    ][$accent] ?? 'text-slate-900';
@endphp

<div class="card">
    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold {{ $ring }}">{{ $value }}</p>
    @if ($sub)
        <p class="mt-1 text-sm text-slate-500">{{ $sub }}</p>
    @endif
</div>
