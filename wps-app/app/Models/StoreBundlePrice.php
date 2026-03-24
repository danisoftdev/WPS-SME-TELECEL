<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreBundlePrice extends Model
{
    protected $fillable = [
        'store_id', 'bundle_subscription_id', 'selling_price',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
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
