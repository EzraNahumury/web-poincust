<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirthdayFollowup extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONTACTED = 'contacted';

    protected $fillable = [
        'customer_id',
        'birthday_year',
        'status',
        'followed_up_by',
        'followed_up_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'followed_up_at' => 'datetime',
            'birthday_year' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'followed_up_by');
    }
}
