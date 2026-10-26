<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Admin / cashier account. Authenticated on the default `web` guard.
 * Customers are a completely separate model + guard.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->name === self::ROLE_SUPER_ADMIN;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Authorisation check used by the Gate and `permission` middleware.
     * Super admins implicitly hold every permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->role) {
            return false;
        }

        // Eager-loaded + memoised to avoid N+1 during a request.
        return $this->role->permissions
            ->contains(fn (Permission $p) => $p->name === $permission);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
