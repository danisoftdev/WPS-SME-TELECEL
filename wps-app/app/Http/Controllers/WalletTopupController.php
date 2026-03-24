<?php

namespace App\Http\Controllers;

use App\Models\WalletTopup;
use App\Services\PaystackGatewayConfig;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalletTopupController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function create(): View
    {
        return view('wallet.topup');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        $amount = round((float) $request->amount, 2);

        if ($user->wallet && $user->wallet->is_frozen) {
            return back()->withErrors(['wallet' => 'Wallet is frozen. Contact admin.']);
        }

        $reference = 'WPS-' . Str::upper(Str::random(12)) . '-' . time();

        WalletTopup::create([
            'user_id' => $user->id,
            'reference' => $reference,
            'amount' => $amount,
            'currency' => PaystackGatewayConfig::currency(),
            'status' => 'pending',
        ]);

        return redirect()->route('wallet.topups.paystack', ['reference' => $reference]);
    }

    public function paystack(string $reference): View
    {
        $topup = WalletTopup::where('reference', $reference)->firstOrFail();

        if ($topup->user_id !== auth()->id()) {
            abort(404);
        }

        if ($topup->status === 'success') {
            return view('wallet.topup_success', ['topup' => $topup]);
        }

        $publicKey = PaystackGatewayConfig::publicKey();
        if (!$publicKey) {
            abort(500, 'Paystack public key not configured.');
        }

        return view('wallet.paystack', [
            'topup' => $topup,
            'publicKey' => $publicKey,
            'currency' => $topup->currency,
            'callbackUrl' => route('wallet.topups.callback', ['reference' => $reference]),
        ]);
    }

    public function callback(Request $request, string $reference): RedirectResponse
    {
        $topup = WalletTopup::where('reference', $reference)->firstOrFail();

        if ($topup->user_id !== auth()->id()) {
            abort(404);
        }

        if ($topup->status === 'success') {
            return redirect()->route('wallet.show')->with('success', 'Wallet funded successfully.');
        }

        $secretKey = PaystackGatewayConfig::secretKey();
        if (!$secretKey) {
            return redirect()->route('wallet.show')->withErrors(['wallet' => 'Paystack secret key not configured.']);
        }

        $baseUrl = PaystackGatewayConfig::baseUrl();
        $resp = Http::withToken($secretKey)->get("{$baseUrl}/transaction/verify/{$reference}");

        if (!$resp->ok()) {
            $topup->update([
                'status' => 'failed',
                'gateway_response' => $resp->json(),
            ]);
            return redirect()->route('wallet.show')->withErrors(['wallet' => 'Unable to verify Paystack payment.']);
        }

        $data = $resp->json('data');
        $ok = $resp->json('status') === true && is_array($data) && ($data['status'] ?? null) === 'success';

        if (!$ok) {
            $topup->update([
                'status' => 'failed',
                'gateway_response' => $resp->json(),
            ]);
            return redirect()->route('wallet.show')->withErrors(['wallet' => 'Payment not successful.']);
        }

        // Paystack reports amount in the smallest currency unit (kobo/pesewas), divide by 100.
        $paidAmount = ((int) ($data['amount'] ?? 0)) / 100;

        // Basic amount mismatch protection (allow tiny float error).
        if (abs($paidAmount - (float) $topup->amount) > 0.01) {
            $topup->update([
                'status' => 'failed',
                'gateway_response' => $resp->json(),
            ]);
            return redirect()->route('wallet.show')->withErrors(['wallet' => 'Payment amount mismatch.']);
        }

        // Credit wallet and mark topup as successful (idempotent guard above).
        $this->walletService->credit(
            $topup->user,
            (float) $topup->amount,
            'paystack_topup',
            $topup->id,
            "Paystack top-up ({$reference})",
            null
        );

        $topup->update([
            'status' => 'success',
            'gateway_response' => $resp->json(),
            'paid_at' => now(),
        ]);

        return redirect()->route('wallet.show')->with('success', 'Wallet funded successfully.');
    }
}

