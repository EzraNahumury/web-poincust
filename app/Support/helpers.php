<?php

use Illuminate\Support\Carbon;

if (! function_exists('rupiah')) {
    /** Format integer rupiah as "Rp1.234.567". */
    function rupiah(int|float|null $amount): string
    {
        return 'Rp' . number_format((int) $amount, 0, ',', '.');
    }
}

if (! function_exists('points_label')) {
    /** Format a point count as "1.250 poin". */
    function points_label(int|float|null $n): string
    {
        return number_format((int) $n, 0, ',', '.') . ' poin';
    }
}

if (! function_exists('jakarta_date')) {
    /** Parse a value into an Asia/Jakarta Carbon instance. */
    function jakarta_date(mixed $value): Carbon
    {
        return Carbon::parse($value)->timezone('Asia/Jakarta');
    }
}
