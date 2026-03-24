<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSubscription extends Model
{
    /** @var string Web routes use /subscriptions; DB table is user_subscriptions. */
    protected $table = 'user_subscriptions';

    protected $fillable = ['user_id', 'bundle_subscription_id', 'balance_gb', 'beneficiaries'];

    protected function casts(): array
    {
        return [
            'balance_gb' => 'decimal:2',
            'beneficiaries' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bundleSubscription(): BelongsTo
    {
        return $this->belongsTo(BundleSubscription::class, 'bundle_subscription_id');
    }

    public function resellerPlans(): HasMany
    {
        return $this->hasMany(ResellerPlan::class, 'user_subscription_id');
    }
}
