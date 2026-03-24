<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("system_setting.{$key}", 300, function () use ($key) {
            return self::query()->where('key', $key)->first();
        });
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget("system_setting.{$key}");
    }

    public static function setEncrypted(string $key, ?string $plainValue): void
    {
        if ($plainValue === null || trim($plainValue) === '') {
            self::query()->where('key', $key)->delete();
            Cache::forget("system_setting.{$key}");
            return;
        }
        self::set($key, Crypt::encryptString($plainValue));
    }

    public static function getDecrypted(string $key, ?string $default = null): ?string
    {
        $value = self::get($key);
        if ($value === null || $value === '') {
            return $default;
        }
        try {
            return Crypt::decryptString((string) $value);
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function mask(?string $value, int $showLast = 6): string
    {
        if (!$value) {
            return 'Not set';
        }
        $len = mb_strlen($value);
        if ($len <= $showLast) {
            return str_repeat('*', $len);
        }
        return str_repeat('*', $len - $showLast) . mb_substr($value, -$showLast);
    }

    public static function normalizeUrl(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // If user types "danysoftdev.com" (no scheme), make it clickable by defaulting to https.
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\//', $value)) {
            $value = 'https://' . $value;
        }

        return $value;
    }
}
