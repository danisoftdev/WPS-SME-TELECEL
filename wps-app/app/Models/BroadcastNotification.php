<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BroadcastNotification extends Model
{
    protected $table = 'broadcast_notifications';

    protected $fillable = [
        'title', 'message', 'audience', 'role_ids', 'active_from', 'active_until', 'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'role_ids' => 'array',
            'active_from' => 'datetime',
            'active_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(BroadcastNotificationRead::class, 'broadcast_notification_id');
    }

    /** Notifications currently visible by schedule and active flag. */
    public function scopeVisibleAt(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('active_from')->orWhere('active_from', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('active_until')->orWhere('active_until', '>=', $now);
            });
    }
}
