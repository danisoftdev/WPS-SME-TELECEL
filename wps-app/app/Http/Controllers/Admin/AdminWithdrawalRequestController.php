<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\PaystackTransferService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminWithdrawalRequestController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected PaystackTransferService $paystackTransfer
    ) {
        $this->middleware('permission:manage_withdrawals');
    }

    public function index(Request $request): View
    {
        $q = WithdrawalRequest::query()->with('user')->orderByDesc('created_at');
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        $requests = $q->paginate(30)->withQueryString();

        return view('admin.withdrawals.index', compact('requests'));
    }

    public function approve(Request $request, WithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        if ($withdrawalRequest->status !== WithdrawalRequest::STATUS_PENDING) {
            return back()->withErrors(['withdrawal' => 'This request is not pending.']);
        }

        $request->validate(['admin_note' => 'nullable|string|max:500']);

        $user = $withdrawalRequest->user;
        $amount = (float) $withdrawalRequest->amount;

        try {
            $this->walletService->debit(
                $user,
                $amount,
                'withdrawal_request',
                $withdrawalRequest->id,
                'Withdrawal request #'.$withdrawalRequest->id,
                $request->user()
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['withdrawal' => $e->getMessage()]);
        }

        $recipient = $this->paystackTransfer->createMobileMoneyRecipient(
            $withdrawalRequest->momo_phone,
            $withdrawalRequest->momo_account_name
        );

        if (! $recipient['ok']) {
            $this->walletService->credit(
                $user,
                $amount,
                'withdrawal_refund',
                $withdrawalRequest->id,
                'Refund: Paystack recipient failed for withdrawal #'.$withdrawalRequest->id,
                $request->user()
            );
            $withdrawalRequest->update([
                'status' => WithdrawalRequest::STATUS_FAILED,
                'paystack_last_response' => json_encode($recipient),
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
                'admin_note' => $request->admin_note,
            ]);

            return back()->withErrors(['withdrawal' => $recipient['message'] ?? 'Recipient creation failed.']);
        }

        $recipientCode = $recipient['data']['recipient_code'] ?? null;
        if (! is_string($recipientCode) || $recipientCode === '') {
            $this->walletService->credit(
                $user,
                $amount,
                'withdrawal_refund',
                $withdrawalRequest->id,
                'Refund: missing recipient code',
                $request->user()
            );
            $withdrawalRequest->update([
                'status' => WithdrawalRequest::STATUS_FAILED,
                'paystack_last_response' => json_encode($recipient),
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
            ]);

            return back()->withErrors(['withdrawal' => 'Paystack did not return a recipient code.']);
        }

        $transferRef = 'WTH-'.$withdrawalRequest->id.'-'.time();
        $transfer = $this->paystackTransfer->initiateTransfer(
            $recipientCode,
            $amount,
            'Withdrawal #'.$withdrawalRequest->id,
            $transferRef
        );

        if (! $transfer['ok']) {
            $this->walletService->credit(
                $user,
                $amount,
                'withdrawal_refund',
                $withdrawalRequest->id,
                'Refund: Paystack transfer failed for withdrawal #'.$withdrawalRequest->id,
                $request->user()
            );
            $withdrawalRequest->update([
                'status' => WithdrawalRequest::STATUS_FAILED,
                'paystack_recipient_code' => $recipientCode,
                'paystack_last_response' => json_encode($transfer),
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
                'admin_note' => $request->admin_note,
            ]);

            return back()->withErrors(['withdrawal' => $transfer['message'] ?? 'Transfer failed.']);
        }

        $data = $transfer['data'] ?? [];

        $withdrawalRequest->update([
            'status' => WithdrawalRequest::STATUS_PAID,
            'paystack_recipient_code' => $recipientCode,
            'paystack_transfer_reference' => $data['reference'] ?? $transferRef,
            'paystack_transfer_code' => $data['transfer_code'] ?? null,
            'paystack_last_response' => json_encode($transfer),
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'admin_note' => $request->admin_note,
        ]);

        return back()->with('success', 'Withdrawal paid via Paystack transfer.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        if ($withdrawalRequest->status !== WithdrawalRequest::STATUS_PENDING) {
            return back()->withErrors(['withdrawal' => 'This request is not pending.']);
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $withdrawalRequest->update([
            'status' => WithdrawalRequest::STATUS_REJECTED,
            'rejection_reason' => $request->rejection_reason,
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Withdrawal request rejected.');
    }
}
