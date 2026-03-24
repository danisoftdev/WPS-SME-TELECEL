<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopCheckout extends Model
{
    protected $fillable = [
        'store_id', 'bundle_subscription_id', 'selling_price', 'customer_phone',
        'amount_total', 'platform_fee', 'net_to_merchant', 'reference', 'status',
        'paystack_response', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'amount_total' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'net_to_merchant' => 'decimal:2',
            'paystack_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function bundleSubscription(): BelongsTo
    {
        return $this->belongsTo(BundleSubscription::class);
    }
}
