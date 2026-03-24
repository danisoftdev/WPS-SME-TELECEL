<?php

namespace App\Support;

final class Branding
{
    public static function appLogoUrl(): string
    {
        return asset('logo/wps-app-logo.jpeg');
    }

    public static function defaultSeoDescription(): string
    {
        return 'WPS-SME — mobile data bundles for resellers and storefronts. Secure Paystack checkout.';
    }
}
