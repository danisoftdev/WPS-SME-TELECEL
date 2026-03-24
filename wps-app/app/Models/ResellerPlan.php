<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResellerPlan extends Model
{
    protected $fillable = [
        'user_id', 'user_subscription_id', 'data_size_gb', 'price',
        'available_units', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'data_size_gb' => 'decimal:2',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userSubscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'reseller_plan_id');
    }
}
