<?php

return [

    // auto = prefer Argon2id when the PHP build supports it, else bcrypt.
    'driver' => env('PIN_HASH_DRIVER', 'auto'),

    'argon' => [
        'memory_cost' => (int) env('PIN_HASH_MEMORY', 65536),
        'time_cost' => (int) env('PIN_HASH_TIME', 4),
        'threads' => (int) env('PIN_HASH_THREADS', 1),
    ],

    'bcrypt' => [
        'cost' => (int) env('PIN_HASH_BCRYPT_COST', 12),
    ],

    // Brute-force protection for PIN verification.
    'max_attempts' => (int) env('PIN_MAX_ATTEMPTS', 5),
    'lock_minutes' => (int) env('PIN_LOCK_MINUTES', 15),

    // Fixed 6-digit numeric PIN per spec §5.4.
    'length' => 6,
];
