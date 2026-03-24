<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPasswordResetRequestsController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Supplier');
    }

    public function index(Request $request): View
    {
        $query = PasswordResetRequest::query()->orderByDesc('created_at');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        $items = $query->paginate(30)->withQueryString();
        return view('admin.password_resets.index', compact('items'));
    }

    public function show(PasswordResetRequest $passwordResetRequest): View
    {
        $code = $passwordResetRequest->getCodePlain();
        return view('admin.password_resets.show', [
            'item' => $passwordResetRequest,
            'code' => $code,
        ]);
    }

    public function markUsed(PasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        $passwordResetRequest->update([
            'status' => 'used',
            'used_at' => now(),
        ]);
        return back()->with('success', 'Marked as used.');
    }

    public function markExpired(PasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        $passwordResetRequest->update(['status' => 'expired']);
        return back()->with('success', 'Marked as expired.');
    }
}

