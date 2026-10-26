<?php

namespace App\Domain\Auth;

/**
 * Hashes and verifies member PINs.
 *
 * PINs are low-entropy secrets (6 numeric digits), so they are protected with
 * a slow, salted password hash — Argon2id when the PHP build supports it,
 * otherwise bcrypt. PINs are NEVER stored or logged in plaintext, and the hash
 * is never exposed to admins (README §5.4).
 */
class PinHasher
{
    private string $algo;
    private array $options;

    public function __construct(array $config)
    {
        $driver = $config['driver'] ?? 'auto';

        $argonAvailable = defined('PASSWORD_ARGON2ID');

        if ($driver === 'argon2id' || ($driver === 'auto' && $argonAvailable)) {
            $this->algo = PASSWORD_ARGON2ID;
            $this->options = [
                'memory_cost' => $config['argon']['memory_cost'],
                'time_cost' => $config['argon']['time_cost'],
                'threads' => $config['argon']['threads'],
            ];
        } else {
            $this->algo = PASSWORD_BCRYPT;
            $this->options = ['cost' => $config['bcrypt']['cost']];
        }
    }

    public function make(string $pin): string
    {
        return password_hash($pin, $this->algo, $this->options);
    }

    public function check(string $pin, ?string $hash): bool
    {
        if ($hash === null || $hash === '') {
            return false;
        }

        return password_verify($pin, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, $this->algo, $this->options);
    }

    public function algorithm(): string
    {
        return $this->algo === PASSWORD_BCRYPT ? 'bcrypt' : 'argon2id';
    }
}
