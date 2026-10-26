<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Immutable point-movement record. The ledger is the source of truth for a
 * customer's balance. Rows must never be updated or deleted through the app;
 * corrections are made by posting new, offsetting rows.
 */
class PointLedger extends Model
{
    public const TYPE_EARN = 'EARN';
    public const TYPE_REDEEM = 'REDEEM';
    public const TYPE_REVERSAL = 'REVERSAL';
    public const TYPE_ADJUSTMENT_CREDIT = 'ADJUSTMENT_CREDIT';
    public const TYPE_ADJUSTMENT_DEBIT = 'ADJUSTMENT_DEBIT';
    public const TYPE_EXPIRED = 'EXPIRED';

    protected $table = 'point_ledger';

    public const UPDATED_AT = null; // no updated_at column

    protected $fillable = [
        'customer_id',
        'transaction_id',
        'type',
        'points_delta',
        'balance_after',
        'description',
        'created_by',
        'reversal_of_id',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'points_delta' => 'integer',
            'balance_after' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Enforce operational immutability at the model layer.
        static::updating(function () {
            throw new RuntimeException('point_ledger rows are immutable and cannot be updated.');
        });
        static::deleting(function () {
            throw new RuntimeException('point_ledger rows are immutable and cannot be deleted.');
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCredit(): bool
    {
        return $this->points_delta >= 0;
    }
}
