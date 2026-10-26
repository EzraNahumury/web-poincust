<?php

return [

    // Default program parameters. The authoritative runtime values live in the
    // `settings` table (editable by a super admin); these are the fallback used
    // before any override is saved, and in environments without the DB.
    'rupiah_per_point' => (int) env('POINTS_RUPIAH_PER_POINT', 10000),
    'redeem_value' => (int) env('POINTS_REDEEM_VALUE', 500),

    // Settings-table keys.
    'keys' => [
        'rupiah_per_point' => 'points_rupiah_per_point',
        'redeem_value' => 'points_redeem_value',
    ],
];
