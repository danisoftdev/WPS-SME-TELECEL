<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function requestForm(): View
    {
        $supportUrl = SystemSetting::get('account_unfreeze_petition_url');
        return view('auth.forgot', ['support_url' => $supportUrl]);
    }

    public function submitRequest(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        // Always respond the same way (don't leak whether user exists).
        $code = (string) random_int(100000, 999999);

        $reset = new PasswordResetRequest([
            'user_id' => $user?->id,
            'email' => $email,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(20),
            'created_at' => now(),
        ]);
        $reset->setCodePlain($code);
        $reset->save();

        return redirect()
            ->route('password.reset.form')
            ->with('info', 'A reset code request has been sent to admin. Please chat admin for the code, then set a new password.')
            ->with('show_support_popup', true);
    }

    public function resetForm(Request $request): View
    {
        $supportUrl = SystemSetting::get('account_unfreeze_petition_url');
        $showPopup = (bool) session('show_support_popup', false);
        return view('auth.reset', [
            'support_url' => $supportUrl,
            'show_support_popup' => $showPopup,
            'email_prefill' => old('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|min:4|max:12',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = strtolower(trim($request->email));
        $code = trim($request->code);

        $pending = PasswordResetRequest::where('email', $email)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->first();

        if (!$pending || $pending->expires_at->isPast()) {
            if ($pending && $pending->expires_at->isPast()) {
                $pending->update(['status' => 'expired']);
            }
            return back()->withErrors(['code' => 'Invalid or expired code.'])->withInput();
        }

        $plain = $pending->getCodePlain();
        if (!$plain || !hash_equals($plain, $code)) {
            return back()->withErrors(['code' => 'Invalid code.'])->withInput();
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Account not found.'])->withInput();
        }

        $user->update(['password' => Hash::make($request->password)]);

        $pending->update([
            'status' => 'used',
            'used_at' => now(),
        ]);

        return redirect()->route('login')->with('success', 'Password changed successfully. You can now log in.');
    }
}

