<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Get or create wallet for user.
     */
    public function getOrCreateWallet(User $user): Wallet
    {
        $wallet = $user->wallet;
        if (!$wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'is_frozen' => false,
            ]);
        }
        return $wallet;
    }

    /**
     * Debit wallet (e.g. on order placement). Throws if insufficient balance or frozen.
     */
    public function debit(
        User $user,
        float $amount,
        string $referenceType,
        ?int $referenceId = null,
        ?string $description = null,
        ?User $performedBy = null
    ): WalletTransaction {
        return DB::transaction(function () use ($user, $amount, $referenceType, $referenceId, $description, $performedBy) {
            $wallet = $this->getOrCreateWallet($user);
            if ($wallet->is_frozen) {
                throw new \RuntimeException('Wallet is frozen.');
            }
            $balanceBefore = (float) $wallet->balance;
            if ($balanceBefore < $amount) {
                throw new \RuntimeException('Insufficient wallet balance.');
            }
            $balanceAfter = $balanceBefore - $amount;
            $wallet->update(['balance' => $balanceAfter]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransaction::TYPE_DEBIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'performed_by' => $performedBy?->id,
            ]);
        });
    }

    /**
     * Credit wallet (admin top-up or refund).
     */
    public function credit(
        User $user,
        float $amount,
        string $referenceType,
        ?int $referenceId = null,
        ?string $description = null,
        ?User $performedBy = null
    ): WalletTransaction {
        return DB::transaction(function () use ($user, $amount, $referenceType, $referenceId, $description, $performedBy) {
            $wallet = $this->getOrCreateWallet($user);
            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $balanceBefore + $amount;
            $wallet->update(['balance' => $balanceAfter]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => WalletTransaction::TYPE_CREDIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'performed_by' => $performedBy?->id,
            ]);
        });
    }

    /**
     * Refund (credit) for a previous debit (e.g. order refund).
     */
    public function refund(
        User $user,
        float $amount,
        string $referenceType,
        ?int $referenceId = null,
        ?string $description = null,
        ?User $performedBy = null
    ): WalletTransaction {
        return $this->credit($user, $amount, $referenceType, $referenceId, $description ?? 'Refund', $performedBy);
    }

    /**
     * Freeze or unfreeze wallet (admin).
     */
    public function setFrozen(User $user, bool $frozen): void
    {
        DB::transaction(function () use ($user, $frozen) {
            $wallet = $this->getOrCreateWallet($user);
            $wallet->update(['is_frozen' => $frozen]);
            $user->update(['is_frozen' => $frozen]);
        });
    }
}
