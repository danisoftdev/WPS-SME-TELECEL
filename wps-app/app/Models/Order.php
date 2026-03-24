<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    public const FINAL_STATUSES = [self::STATUS_SENT, self::STATUS_REFUNDED];

    protected $fillable = [
        'user_id',
        'store_id',
        'sub_agent_user_id',
        'phone_number',
        'reseller_plan_id',
        'amount',
        'status',
        'internal_notes',
        'igate_submitted_at',
        'igate_last_http_code',
        'igate_last_error',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'igate_submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function subAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sub_agent_user_id');
    }

    public function resellerPlan(): BelongsTo
    {
        return $this->belongsTo(ResellerPlan::class, 'reseller_plan_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderByDesc('created_at');
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL_STATUSES, true);
    }
}
