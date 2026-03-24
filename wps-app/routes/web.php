<?php

use App\Http\Controllers\Admin\AdminBundleController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminPasswordResetRequestsController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminSystemSettingsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminWalletController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ResellerPlanController;
use App\Http\Controllers\UserSubscriptionController;
use App\Http\Controllers\Admin\AdminStoreController;
use App\Http\Controllers\Admin\AdminWithdrawalRequestController;
use App\Http\Controllers\PayoutProfileController;
use App\Http\Controllers\PublicShopController;
use App\Http\Controllers\ShopCheckoutController;
use App\Http\Controllers\StoreBundlePricingController;
use App\Http\Controllers\StoreOwnerProfileController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreJoinController;
use App\Http\Controllers\SubAgentPricingController;
use App\Http\Controllers\WithdrawalRequestController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletTopupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route(auth()->user()->hasRole('Supplier') ? 'admin.dashboard' : 'dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'requestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'submitRequest'])->name('password.request.submit');
    Route::get('reset-password', [ForgotPasswordController::class, 'resetForm'])->name('password.reset.form');
    Route::post('reset-password', [ForgotPasswordController::class, 'reset'])->name('password.reset.submit');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('join-store/{token}', [StoreJoinController::class, 'show'])->name('stores.join');
Route::post('join-store/{token}', [StoreJoinController::class, 'accept'])->middleware('auth')->name('stores.join.accept');

Route::get('shop/{store}', [PublicShopController::class, 'show'])->name('shop.show');
Route::post('shop/{store}/checkout', [ShopCheckoutController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('shop.checkout.store');
Route::get('shop/checkout/{reference}/pay', [ShopCheckoutController::class, 'paystack'])->name('shop.checkout.pay');
Route::get('shop/checkout/{reference}/callback', [ShopCheckoutController::class, 'callback'])->name('shop.checkout.callback');

Route::middleware(['auth'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
});

Route::middleware(['auth', 'permission:place_orders|manage_orders'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('wallet', [WalletController::class, 'show'])->name('wallet.show');
    Route::get('wallet/topups/{reference}', [WalletTopupController::class, 'paystack'])->name('wallet.topups.paystack');
    Route::get('wallet/topups/{reference}/callback', [WalletTopupController::class, 'callback'])->name('wallet.topups.callback');
    Route::post('wallet/topups', [WalletTopupController::class, 'store'])->name('wallet.topups.store');
    Route::get('orders/repeat', [OrderController::class, 'repeatLast'])->name('orders.repeat');
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
});

Route::middleware(['auth', 'permission:manage_reseller_plans'])->group(function () {
    Route::resource('plans', ResellerPlanController::class)->except(['show']);
});

Route::middleware(['auth'])->group(function () {
    Route::resource('subscriptions', UserSubscriptionController::class)->only(['index', 'create', 'store', 'edit', 'update']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('stores/wallet-snapshot', [StoreController::class, 'walletSnapshot'])->name('stores.wallet-snapshot');
    Route::get('stores/{store}/pricing', [StoreBundlePricingController::class, 'edit'])->name('stores.pricing.edit');
    Route::put('stores/{store}/pricing', [StoreBundlePricingController::class, 'update'])->name('stores.pricing.update');
    Route::resource('stores', StoreController::class)->except(['show']);
    Route::get('store-owner/profile', [StoreOwnerProfileController::class, 'edit'])->name('store-owner.profile');
    Route::put('store-owner/profile', [StoreOwnerProfileController::class, 'update'])->name('store-owner.profile.update');
    Route::get('payout', [PayoutProfileController::class, 'edit'])->name('payout.edit');
    Route::put('payout', [PayoutProfileController::class, 'update'])->name('payout.update');
    Route::get('withdrawals', [WithdrawalRequestController::class, 'index'])->name('withdrawals.index');
    Route::get('withdrawals/create', [WithdrawalRequestController::class, 'create'])->name('withdrawals.create');
    Route::post('withdrawals', [WithdrawalRequestController::class, 'store'])->name('withdrawals.store');
});

Route::middleware(['auth', 'permission:set_sub_agent_prices'])->group(function () {
    Route::get('my-store/pricing', [SubAgentPricingController::class, 'index'])->name('sub-agent.pricing.index');
    Route::put('my-store/pricing', [SubAgentPricingController::class, 'update'])->name('sub-agent.pricing.update');
});

Route::middleware(['auth', 'permission:access_api'])->group(function () {
    Route::get('api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Supplier|Admin'])->group(function () {
    Route::middleware('role:Supplier')->group(function () {
        Route::resource('stores', AdminStoreController::class)->except(['show']);
    });

    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show'])->names('orders');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('orders/bulk-status', [AdminOrderController::class, 'bulkUpdate'])->name('orders.bulkUpdate');
    Route::put('orders/{order}/notes', [AdminOrderController::class, 'updateNotes'])->name('orders.updateNotes');
    Route::get('wallets', [AdminWalletController::class, 'index'])->name('wallets.index');
    Route::get('wallets/{user}', [AdminWalletController::class, 'show'])->name('wallets.show');
    Route::post('wallets/{user}/credit', [AdminWalletController::class, 'credit'])->name('wallets.credit');
    Route::post('wallets/{user}/debit', [AdminWalletController::class, 'debit'])->name('wallets.debit');
    Route::post('wallets/{user}/freeze', [AdminWalletController::class, 'freeze'])->name('wallets.freeze');
    Route::post('wallets/{user}/unfreeze', [AdminWalletController::class, 'unfreeze'])->name('wallets.unfreeze');
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::resource('bundles', AdminBundleController::class)->parameters(['bundles' => 'bundle']);
    Route::resource('notifications', AdminNotificationController::class)->parameters(['notifications' => 'notification']);
    Route::resource('roles', AdminRoleController::class)->except(['show']);
    Route::get('settings', [AdminSystemSettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [AdminSystemSettingsController::class, 'update'])->name('settings.update');

    Route::get('password-resets', [AdminPasswordResetRequestsController::class, 'index'])->name('passwordResets.index');
    Route::get('password-resets/{passwordResetRequest}', [AdminPasswordResetRequestsController::class, 'show'])->name('passwordResets.show');
    Route::post('password-resets/{passwordResetRequest}/used', [AdminPasswordResetRequestsController::class, 'markUsed'])->name('passwordResets.used');
    Route::post('password-resets/{passwordResetRequest}/expired', [AdminPasswordResetRequestsController::class, 'markExpired'])->name('passwordResets.expired');

    Route::get('withdrawals', [AdminWithdrawalRequestController::class, 'index'])->name('withdrawals.index');
    Route::post('withdrawals/{withdrawalRequest}/approve', [AdminWithdrawalRequestController::class, 'approve'])->name('withdrawals.approve');
    Route::post('withdrawals/{withdrawalRequest}/reject', [AdminWithdrawalRequestController::class, 'reject'])->name('withdrawals.reject');
});
