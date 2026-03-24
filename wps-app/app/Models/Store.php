<?php

namespace App\Models;

use App\Support\Branding;
use App\Support\StoreLogoUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Store extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'whatsapp_phone', 'contact_email', 'location', 'logo_path',
        'slug', 'invite_token', 'is_active',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Store $store) {
            StoreLogoUpload::deleteIfPresent($store->logo_path);
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function customerShopUrl(): string
    {
        return url('/shop/'.$this->slug);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subAgents(): HasMany
    {
        return $this->hasMany(User::class, 'store_id');
    }

    public function subAgentBundlePrices(): HasMany
    {
        return $this->hasMany(SubAgentBundlePrice::class, 'store_id');
    }

    public function bundlePrices(): HasMany
    {
        return $this->hasMany(StoreBundlePrice::class, 'store_id');
    }

    public function hasCustomLogo(): bool
    {
        return $this->logo_path !== null
            && $this->logo_path !== ''
            && Storage::disk('public')->exists($this->logo_path);
    }

    /** Public URL for <img> (null if no logo). */
    public function logoDisplayUrl(): ?string
    {
        if (! $this->hasCustomLogo()) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    /** Absolute image URL for OG / Twitter / favicon on store pages. */
    public function seoOgImageUrl(): string
    {
        if ($this->hasCustomLogo()) {
            return Storage::disk('public')->url($this->logo_path);
        }

        return Branding::appLogoUrl();
    }
}
