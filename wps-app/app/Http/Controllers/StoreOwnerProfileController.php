<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreOwnerProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if ($user->isSupplier()) {
            abort(403, 'MoMo payout profile is not used for super admin accounts.');
        }
        if (! $user->ownsAnyStore()) {
            return redirect()->route('stores.index')
                ->with('info', 'Create a store first. Your MoMo details for withdrawals are managed here as part of your store owner profile.');
        }

        $stores = $user->ownedStores()->orderBy('name')->get();

        return view('store-owner.profile', compact('user', 'stores'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->isSupplier()) {
            abort(403);
        }
        if (! $user->ownsAnyStore()) {
            return redirect()->route('stores.index')
                ->with('info', 'Create a store before saving payout details.');
        }

        $request->validate([
            'momo_phone' => 'required|string|max:32',
            'momo_account_name' => 'required|string|max:120',
        ]);

        $user->update([
            'momo_phone' => $request->momo_phone,
            'momo_account_name' => $request->momo_account_name,
        ]);

        return redirect()->route('store-owner.profile')->with('success', 'Store owner payout details saved.');
    }
}
