@props(['tone' => 'dark', 'size' => 'md'])

@php
    $word = $tone === 'light' ? 'text-white' : 'text-ink-900';
    $dim = [
        'sm' => ['h-7 w-7 text-sm rounded-lg', 'text-base'],
        'md' => ['h-9 w-9 text-lg rounded-xl', 'text-xl'],
        'lg' => ['h-11 w-11 text-xl rounded-2xl', 'text-2xl'],
    ][$size] ?? ['h-9 w-9 text-lg rounded-xl', 'text-xl'];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-2 font-extrabold tracking-tight']) }}>
    <span class="flex items-center justify-center bg-brand-600 font-black text-white shadow-sm {{ $dim[0] }}">A</span>
    <span class="{{ $word }} {{ $dim[1] }}">Ava<span class="text-brand-500">sport</span></span>
</span>
