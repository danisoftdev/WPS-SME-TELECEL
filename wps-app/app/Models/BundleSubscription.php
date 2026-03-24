<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BundleSubscription extends Model
{
    protected $fillable = ['name', 'network', 'total_data_gb', 'amount', 'max_beneficiaries', 'is_active'];

    protected function casts(): array
    {
        return [
            'total_data_gb' => 'decimal:2',
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'bundle_subscription_id');
    }

    public function subAgentBundlePrices(): HasMany
    {
        return $this->hasMany(SubAgentBundlePrice::class, 'bundle_subscription_id');
    }

    public function storeBundlePrices(): HasMany
    {
        return $this->hasMany(StoreBundlePrice::class, 'bundle_subscription_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeNetwork(Builder $query, string $network): Builder
    {
        return $query->where('network', $network);
    }
}
