<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * A membership customer. Authenticated on the dedicated `customer` guard
 * (username + password). The PIN is a SEPARATE secret, never a login credential.
 */
class Customer extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_number',
        'name',
        'birth_date',
        'whatsapp_number',
        'username',
        'email',
        'password',
        'pin_hash',
        'status',
    ];

    /** Secrets and internal counters never leave the server in serialized form. */
    protected $hidden = [
        'password',
        'pin_hash',
        'remember_token',
        'pin_attempts',
        'pin_locked_until',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'pin_locked_until' => 'datetime',
            'points_balance' => 'integer',
            'pin_attempts' => 'integer',
            // Login password hashed with the framework default (bcrypt).
            // pin_hash is handled separately via the Argon2id PinHasher.
            'password' => 'hashed',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(PointLedger::class)->orderByDesc('id');
    }

    public function birthdayFollowups(): HasMany
    {
        return $this->hasMany(BirthdayFollowup::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPinLocked(): bool
    {
        return $this->pin_locked_until !== null && $this->pin_locked_until->isFuture();
    }

    /** Normalise a username so case variants cannot create duplicates. */
    public static function normalizeUsername(string $username): string
    {
        return mb_strtolower(trim($username));
    }

    /** Partially masked WhatsApp number for lower-privilege display. */
    public function maskedWhatsapp(): string
    {
        $n = $this->whatsapp_number ?? '';
        if (strlen($n) <= 5) {
            return $n;
        }

        return substr($n, 0, 4) . str_repeat('*', max(0, strlen($n) - 7)) . substr($n, -3);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
