<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REVERSED = 'reversed';

    protected $fillable = [
        'transaction_number',
        'customer_id',
        'created_by',
        'transaction_date',
        'gross_amount',
        'transaction_discount',
        'eligible_amount',
        'points_redeemed',
        'redemption_discount',
        'points_earned',
        'final_amount',
        'status',
        'notes',
        'external_receipt_no',
        'reversed_transaction_id',
        'reversal_reason',
        'reversed_by',
        'reversed_at',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'reversed_at' => 'datetime',
            'gross_amount' => 'integer',
            'transaction_discount' => 'integer',
            'eligible_amount' => 'integer',
            'points_redeemed' => 'integer',
            'redemption_discount' => 'integer',
            'points_earned' => 'integer',
            'final_amount' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(PointLedger::class);
    }

    public function isReversed(): bool
    {
        return $this->status === self::STATUS_REVERSED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
