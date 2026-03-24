<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiOrderController;
use App\Http\Controllers\Api\ApiPayoutProfileController;
use App\Http\Controllers\Api\ApiPlanController;
use App\Http\Controllers\Api\ApiPublicShopController;
use App\Http\Controllers\Api\ApiStoreController;
use App\Http\Controllers\Api\ApiStorePricingController;
use App\Http\Controllers\Api\ApiWalletController;
use App\Http\Controllers\Api\ApiWalletTopupController;
use App\Http\Controllers\Api\ApiWithdrawalController;
use App\Http\Controllers\DeployMigrationController;
use App\Http\Controllers\IgateWebhookController;
use App\Http\Middleware\VerifyIgateWebhook;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Sanctum token auth + abilities + throttling)
|--------------------------------------------------------------------------
*/

Route::post('/login', [ApiAuthController::class, 'login'])->middleware('throttle:api-login');

/** No SSH: set DEPLOY_MIGRATION_TOKEN in .env (32+ chars), POST once, then remove token. */
Route::get('/v1/deploy/migrate', [DeployMigrationController::class, 'instructions'])
    ->middleware('throttle:api-deploy-migrate');
Route::post('/v1/deploy/migrate', [DeployMigrationController::class, 'migrate'])
    ->middleware('throttle:api-deploy-migrate');

Route::post('/webhooks/igate', IgateWebhookController::class)
    ->middleware(['throttle:webhook-igate', VerifyIgateWebhook::class]);

/** Public store catalog & guest checkout (no token; uses admin-configured Paystack on pay_url) */
Route::prefix('v1')->middleware('throttle:api-public-shop')->group(function () {
    Route::get('shops/{slug}/catalog', [ApiPublicShopController::class, 'catalog'])->name('api.v1.shops.catalog');
    Route::post('shops/{slug}/checkout', [ApiPublicShopController::class, 'checkout'])->name('api.v1.shops.checkout');
});

Route::middleware(['auth:sanctum', 'throttle:api-v1'])->prefix('v1')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout'])->middleware('api_ability:account:read');
    Route::get('/me', [ApiAuthController::class, 'me'])->middleware('api_ability:account:read');

    Route::get('/wallet', [ApiWalletController::class, 'show'])->middleware('api_ability:wallet:read');
    Route::get('/wallet/transactions', [ApiWalletController::class, 'transactions'])->middleware('api_ability:wallet:transactions:read');
    Route::post('/wallet/topups', [ApiWalletTopupController::class, 'store'])->middleware('api_ability:wallet:topup');
    Route::get('/wallet/topups/{reference}', [ApiWalletTopupController::class, 'show'])->middleware('api_ability:wallet:read');

    Route::get('/plans', [ApiPlanController::class, 'index'])->middleware('api_ability:plans:read');
    Route::get('/subscriptions', [ApiPlanController::class, 'subscriptions'])->middleware('api_ability:plans:subscriptions:read');

    Route::get('/orders', [ApiOrderController::class, 'index'])->middleware('api_ability:orders:read');
    Route::get('/orders/{order}', [ApiOrderController::class, 'show'])->middleware('api_ability:orders:read');
    Route::post('/orders', [ApiOrderController::class, 'store'])->middleware('api_ability:orders:write');

    Route::get('/stores', [ApiStoreController::class, 'index'])->middleware('api_ability:stores:read');
    Route::post('/stores', [ApiStoreController::class, 'store'])->middleware('api_ability:stores:write');
    Route::get('/stores/{store}', [ApiStoreController::class, 'show'])->middleware('api_ability:stores:read');
    Route::put('/stores/{store}', [ApiStoreController::class, 'update'])->middleware('api_ability:stores:write');
    Route::delete('/stores/{store}', [ApiStoreController::class, 'destroy'])->middleware('api_ability:stores:write');
    Route::get('/stores/{store}/pricing', [ApiStorePricingController::class, 'show'])->middleware('api_ability:stores:read');
    Route::put('/stores/{store}/pricing', [ApiStorePricingController::class, 'update'])->middleware('api_ability:stores:write');

    Route::get('/payout', [ApiPayoutProfileController::class, 'show'])->middleware('api_ability:payout:read');
    Route::put('/payout', [ApiPayoutProfileController::class, 'update'])->middleware('api_ability:payout:write');

    Route::get('/withdrawals', [ApiWithdrawalController::class, 'index'])->middleware('api_ability:withdrawals:read');
    Route::post('/withdrawals', [ApiWithdrawalController::class, 'store'])->middleware('api_ability:withdrawals:write');
});
