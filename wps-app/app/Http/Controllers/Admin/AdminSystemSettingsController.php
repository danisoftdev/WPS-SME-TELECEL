<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\PaystackGatewayConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSystemSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_system_settings');
    }

    public function index(): View
    {
        $maxPending = SystemSetting::get('max_pending_orders_per_user', 10);
        $paystackPublicMasked = SystemSetting::mask(PaystackGatewayConfig::publicKey());
        $paystackSecretMasked = SystemSetting::mask(PaystackGatewayConfig::secretKey());
        $unfreezeUrl = SystemSetting::get('account_unfreeze_petition_url');

        return view('admin.settings.index', [
            'max_pending_orders_per_user' => $maxPending,
            'paystack_public_masked' => $paystackPublicMasked,
            'paystack_secret_masked' => $paystackSecretMasked,
            'account_unfreeze_petition_url' => $unfreezeUrl,
            'paystack_base_url_saved' => SystemSetting::get('paystack_base_url') ?? '',
            'paystack_currency_saved' => SystemSetting::get('paystack_currency') ?? '',
            'paystack_momo_bank_code_saved' => SystemSetting::get('paystack_momo_bank_code') ?? '',
            'paystack_effective_base_url' => PaystackGatewayConfig::baseUrl(),
            'paystack_effective_currency' => PaystackGatewayConfig::currency(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $section = (string) $request->input('section', '');

        if ($section === 'account') {
            if (!$request->user()->hasRole('Supplier')) {
                abort(403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $request->user()->id,
                'phone' => 'nullable|string|max:20',
                'current_password' => 'nullable|string',
                'new_password' => 'nullable|string|min:8|confirmed',
            ]);

            $user = $request->user();
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            if ($request->filled('new_password')) {
                if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors(['current_password' => 'Current password is incorrect.']);
                }
                $user->update(['password' => Hash::make($request->new_password)]);
            }

            return back()->with('success', 'Account settings saved.');
        }

        if ($section === 'limits') {
            $request->validate([
                'max_pending_orders_per_user' => 'required|integer|min:1|max:100',
            ]);
            SystemSetting::set('max_pending_orders_per_user', $request->max_pending_orders_per_user);
            return back()->with('success', 'Limits saved.');
        }

        if ($section === 'paystack') {
            $request->validate([
                'paystack_public_key' => 'nullable|string|max:255',
                'paystack_secret_key' => 'nullable|string|max:255',
                'paystack_base_url' => 'nullable|string|max:255',
                'paystack_currency' => 'nullable|string|max:3',
                'paystack_momo_bank_code' => 'nullable|string|max:32',
            ]);
            if ($request->filled('paystack_public_key')) {
                SystemSetting::setEncrypted('paystack_public_key', $request->paystack_public_key);
            }
            if ($request->filled('paystack_secret_key')) {
                SystemSetting::setEncrypted('paystack_secret_key', $request->paystack_secret_key);
            }
            if ($request->has('paystack_base_url')) {
                $u = trim((string) $request->paystack_base_url);
                if ($u === '') {
                    SystemSetting::query()->where('key', 'paystack_base_url')->delete();
                    Cache::forget('system_setting.paystack_base_url');
                } elseif (! filter_var($u, FILTER_VALIDATE_URL)) {
                    return back()->withErrors(['paystack_base_url' => 'Enter a valid URL (e.g. https://api.paystack.co).']);
                } else {
                    SystemSetting::set('paystack_base_url', rtrim($u, '/'));
                }
            }
            if ($request->has('paystack_currency')) {
                $c = strtoupper(trim((string) $request->paystack_currency));
                if ($c === '') {
                    SystemSetting::query()->where('key', 'paystack_currency')->delete();
                    Cache::forget('system_setting.paystack_currency');
                } elseif (! preg_match('/^[A-Z]{3}$/', $c)) {
                    return back()->withErrors(['paystack_currency' => 'Use a 3-letter currency code (e.g. GHS).']);
                } else {
                    SystemSetting::set('paystack_currency', $c);
                }
            }
            if ($request->has('paystack_momo_bank_code')) {
                $m = trim((string) $request->paystack_momo_bank_code);
                if ($m === '') {
                    SystemSetting::query()->where('key', 'paystack_momo_bank_code')->delete();
                    Cache::forget('system_setting.paystack_momo_bank_code');
                } else {
                    SystemSetting::set('paystack_momo_bank_code', $m);
                }
            }

            return back()->with('success', 'Paystack settings saved. Wallet top-ups, shop checkout, and MoMo withdrawals use these keys.');
        }

        if ($section === 'unfreeze') {
            $request->validate([
                'account_unfreeze_petition_url' => 'nullable|string|max:2048',
            ]);
            $normalized = SystemSetting::normalizeUrl($request->account_unfreeze_petition_url);
            if ($normalized === null) {
                SystemSetting::query()->where('key', 'account_unfreeze_petition_url')->delete();
            } else {
                SystemSetting::set('account_unfreeze_petition_url', $normalized);
            }
            return back()->with('success', 'Unfreeze link saved.');
        }

        return back()->withErrors(['settings' => 'Invalid settings section.']);
    }
}
