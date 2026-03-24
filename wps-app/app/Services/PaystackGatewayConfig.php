<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Crypt;

/**
 * Paystack public/secret keys and API options: read from system_settings (admin dashboard) first, then config/.env.
 */
final class PaystackGatewayConfig
{
    public static function publicKey(): ?string
    {
        $v = self::resolveCredential('paystack_public_key');

        return ($v !== null && $v !== '') ? $v : config('services.paystack.public_key');
    }

    public static function secretKey(): ?string
    {
        $v = self::resolveCredential('paystack_secret_key');

        return ($v !== null && $v !== '') ? $v : config('services.paystack.secret_key');
    }

    public static function baseUrl(): string
    {
        $url = SystemSetting::get('paystack_base_url');
        if (is_string($url) && trim($url) !== '') {
            return rtrim(trim($url), '/');
        }

        return rtrim((string) config('services.paystack.base_url', 'https://api.paystack.co'), '/');
    }

    public static function currency(): string
    {
        $c = SystemSetting::get('paystack_currency');
        if (is_string($c) && preg_match('/^[A-Za-z]{3}$/', trim($c))) {
            return strtoupper(trim($c));
        }

        return (string) config('services.paystack.currency', 'GHS');
    }

    public static function momoBankCode(): string
    {
        $code = SystemSetting::get('paystack_momo_bank_code');
        if (is_string($code) && trim($code) !== '') {
            return trim($code);
        }

        return (string) config('services.paystack.momo_bank_code', 'MTN');
    }

    /**
     * @param  non-empty-string  $key
     */
    private static function resolveCredential(string $key): ?string
    {
        $stored = SystemSetting::get($key);
        if ($stored === null || $stored === '') {
            return null;
        }
        $stored = trim((string) $stored);
        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable) {
            if (str_starts_with($stored, 'pk_') || str_starts_with($stored, 'sk_')) {
                return $stored;
            }

            return null;
        }
    }
}
