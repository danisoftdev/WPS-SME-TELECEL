<?php

namespace App\Http\Controllers;

use App\Models\ShopCheckout;
use App\Models\Store;
use App\Services\PaystackGatewayConfig;
use App\Services\ShopCheckoutService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ShopCheckoutController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected ShopCheckoutService $shopCheckoutService
    ) {}

    public function store(Request $request, Store $store): RedirectResponse
    {
        $request->validate([
            'bundle_subscription_id' => 'required|integer|exists:bundle_subscriptions,id',
            'customer_phone' => 'required|string|max:32',
        ]);

        try {
            $checkout = $this->shopCheckoutService->createPendingCheckout(
                $store,
                $request->integer('bundle_subscription_id'),
                $request->customer_phone
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('shop.checkout.pay', ['reference' => $checkout->reference]);
    }

    public function paystack(string $reference): View
    {
        $checkout = ShopCheckout::query()->where('reference', $reference)->with(['store.owner'])->firstOrFail();

        if ($checkout->status === 'success') {
            return $this->checkoutSuccessView($checkout);
        }

        $publicKey = PaystackGatewayConfig::publicKey();
        if (! $publicKey) {
            abort(500, 'Paystack public key not configured.');
        }

        $store = $checkout->store;
        $email = $store->contact_email ?: $store->owner?->email ?: config('mail.from.address', 'noreply@example.com');
        $currency = PaystackGatewayConfig::currency();

        return view('shop.paystack', [
            'checkout' => $checkout,
            'publicKey' => $publicKey,
            'payEmail' => $email,
            'currency' => $currency,
            'callbackUrl' => route('shop.checkout.callback', ['reference' => $reference]),
        ]);
    }

    public function callback(Request $request, string $reference): RedirectResponse
    {
        $checkout = ShopCheckout::query()->where('reference', $reference)->with('store.owner')->firstOrFail();

        if ($checkout->status === 'success') {
            return redirect()->route('shop.show', $checkout->store)->with('success', 'Payment already completed.');
        }

        $secretKey = PaystackGatewayConfig::secretKey();
        if (! $secretKey) {
            return redirect()->route('shop.show', $checkout->store)->withErrors(['pay' => 'Payment verification unavailable.']);
        }

        $baseUrl = PaystackGatewayConfig::baseUrl();
        $resp = Http::withToken($secretKey)->get("{$baseUrl}/transaction/verify/{$reference}");

        if (! $resp->ok()) {
            $checkout->update(['status' => 'failed', 'paystack_response' => $resp->json()]);

            return redirect()->route('shop.show', $checkout->store)->withErrors(['pay' => 'Unable to verify payment.']);
        }

        $data = $resp->json('data');
        $ok = $resp->json('status') === true && is_array($data) && ($data['status'] ?? null) === 'success';

        if (! $ok) {
            $checkout->update(['status' => 'failed', 'paystack_response' => $resp->json()]);

            return redirect()->route('shop.show', $checkout->store)->withErrors(['pay' => 'Payment was not successful.']);
        }

        $paidAmount = ((int) ($data['amount'] ?? 0)) / 100;
        if (abs($paidAmount - (float) $checkout->amount_total) > 0.02) {
            $checkout->update(['status' => 'failed', 'paystack_response' => $resp->json()]);

            return redirect()->route('shop.show', $checkout->store)->withErrors(['pay' => 'Payment amount mismatch.']);
        }

        $owner = $checkout->store->owner;
        if (! $owner) {
            $checkout->update(['status' => 'failed', 'paystack_response' => $resp->json()]);

            return redirect()->route('shop.show', $checkout->store)->withErrors(['pay' => 'Store owner missing.']);
        }

        $payload = $resp->json();

        DB::transaction(function () use ($checkout, $owner, $reference, $payload) {
            $locked = ShopCheckout::query()
                ->where('id', $checkout->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                return;
            }

            $this->walletService->credit(
                $owner,
                (float) $locked->net_to_merchant,
                'shop_sale',
                $locked->id,
                'Shop checkout '.$reference.' (customer '.$locked->customer_phone.')',
                null
            );

            $locked->update([
                'status' => 'success',
                'paystack_response' => $payload,
                'paid_at' => now(),
            ]);
        });

        $checkout->refresh();

        if ($checkout->status !== 'success') {
            return redirect()->route('shop.show', $checkout->store)->withErrors(['pay' => 'Could not finalize payment. Contact support if you were charged.']);
        }

        return redirect()->route('shop.checkout.pay', $reference)->with('success', 'Thank you! The store owner has been notified.');
    }

    private function checkoutSuccessView(ShopCheckout $checkout): View
    {
        $checkout->load('store');
        $store = $checkout->store;
        $seoTitle = 'Payment successful · '.$store->name.' | WPS-SME';
        $seoDescription = 'Your payment to '.$store->name.' was successful.';
        $seoImage = $store->seoOgImageUrl();
        $seoCanonicalUrl = route('shop.checkout.pay', ['reference' => $checkout->reference]);
        $navShopStore = $store;

        return view('shop.checkout_success', compact(
            'checkout',
            'seoTitle',
            'seoDescription',
            'seoImage',
            'seoCanonicalUrl',
            'navShopStore'
        ));
    }
}
