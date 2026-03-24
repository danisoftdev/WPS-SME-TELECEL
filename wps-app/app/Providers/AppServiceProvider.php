<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(10)->by('api-login|'.$request->ip());
        });

        RateLimiter::for('api-v1', function (Request $request) {
            $key = $request->user()?->id ?? $request->ip();

            return Limit::perMinute(120)->by('api-v1|'.$key);
        });

        RateLimiter::for('webhook-igate', function (Request $request) {
            return Limit::perMinute(120)->by('webhook-igate|'.$request->ip());
        });

        RateLimiter::for('api-public-shop', function (Request $request) {
            return Limit::perMinute(60)->by('api-public-shop|'.$request->ip());
        });

        RateLimiter::for('api-deploy-migrate', function (Request $request) {
            return Limit::perHour(10)->by('api-deploy-migrate|'.$request->ip());
        });

        // Ensure generated URLs use APP_URL when set (important when app is in subdir or behind proxy)
        $appUrl = config('app.url');
        if ($appUrl && $this->app->environment('production')) {
            URL::forceRootUrl($appUrl);
            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }
    }
}
