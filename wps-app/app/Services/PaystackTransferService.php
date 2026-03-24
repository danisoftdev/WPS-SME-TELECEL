<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackTransferService
{
    protected function baseUrl(): string
    {
        return PaystackGatewayConfig::baseUrl();
    }

    public function secretKey(): ?string
    {
        return PaystackGatewayConfig::secretKey();
    }

    /**
     * @return array{ok: bool, data?: array, message?: string}
     */
    public function createMobileMoneyRecipient(string $phone, string $accountName, string $currency = 'GHS'): array
    {
        $secret = $this->secretKey();
        if (! $secret) {
            return ['ok' => false, 'message' => 'Paystack secret key not configured.'];
        }

        $bankCode = PaystackGatewayConfig::momoBankCode();
        $normalized = preg_replace('/\D+/', '', $phone) ?? $phone;

        $resp = Http::withToken($secret)
            ->acceptJson()
            ->post($this->baseUrl().'/transferrecipient', [
                'type' => 'mobile_money',
                'name' => mb_substr($accountName, 0, 100),
                'account_number' => $normalized,
                'bank_code' => $bankCode,
                'currency' => $currency,
            ]);

        $json = $resp->json();
        if (! $resp->ok() || ($json['status'] ?? false) !== true) {
            Log::warning('Paystack transferrecipient failed', ['body' => $json]);

            return ['ok' => false, 'message' => $json['message'] ?? 'Recipient creation failed', 'data' => $json];
        }

        return ['ok' => true, 'data' => $json['data'] ?? []];
    }

    /**
     * @return array{ok: bool, data?: array, message?: string}
     */
    public function initiateTransfer(string $recipientCode, float $amountGhs, string $reason, string $reference): array
    {
        $secret = $this->secretKey();
        if (! $secret) {
            return ['ok' => false, 'message' => 'Paystack secret key not configured.'];
        }

        $amountPesewas = (int) round($amountGhs * 100);
        if ($amountPesewas < 1) {
            return ['ok' => false, 'message' => 'Amount too small.'];
        }

        $resp = Http::withToken($secret)
            ->acceptJson()
            ->post($this->baseUrl().'/transfer', [
                'source' => 'balance',
                'amount' => $amountPesewas,
                'recipient' => $recipientCode,
                'reason' => mb_substr($reason, 0, 200),
                'reference' => mb_substr($reference, 0, 100),
            ]);

        $json = $resp->json();
        if (! $resp->ok() || ($json['status'] ?? false) !== true) {
            Log::warning('Paystack transfer failed', ['body' => $json]);

            return ['ok' => false, 'message' => $json['message'] ?? 'Transfer failed', 'data' => $json];
        }

        return ['ok' => true, 'data' => $json['data'] ?? []];
    }
}
