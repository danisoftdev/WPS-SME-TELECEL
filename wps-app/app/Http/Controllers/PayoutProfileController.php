<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PayoutProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit(): RedirectResponse
    {
        if (auth()->user()->isSupplier()) {
            abort(403, 'MoMo payout is not used for super admin accounts.');
        }

        return redirect()->route('store-owner.profile');
    }

    public function update(Request $request): RedirectResponse
    {
        return app(StoreOwnerProfileController::class)->update($request);
    }
}
