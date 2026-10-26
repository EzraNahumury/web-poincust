<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Writes immutable audit entries for sensitive actions. Never record
 * passwords, PINs, tokens or other credentials here (README §16.6).
 */
class AuditLogger
{
    /**
     * @param array{
     *   actor_type?:string, actor_id?:int|null, entity_type?:string|null,
     *   entity_id?:int|null, before?:array|null, after?:array|null,
     *   reason?:string|null, ip?:string|null
     * } $opts
     */
    public static function log(string $action, array $opts = []): AuditLog
    {
        return AuditLog::create([
            'actor_type' => $opts['actor_type'] ?? self::currentActorType(),
            'actor_id' => $opts['actor_id'] ?? self::currentActorId(),
            'action' => $action,
            'entity_type' => $opts['entity_type'] ?? null,
            'entity_id' => $opts['entity_id'] ?? null,
            'before_data' => $opts['before'] ?? null,
            'after_data' => $opts['after'] ?? null,
            'reason' => $opts['reason'] ?? null,
            'ip_address' => $opts['ip'] ?? (request()?->ip()),
            'created_at' => now(),
        ]);
    }

    private static function currentActorType(): string
    {
        if (Auth::guard('web')->check()) {
            return 'admin';
        }
        if (Auth::guard('customer')->check()) {
            return 'customer';
        }

        return 'system';
    }

    private static function currentActorId(): ?int
    {
        return Auth::guard('web')->id() ?? Auth::guard('customer')->id();
    }
}
