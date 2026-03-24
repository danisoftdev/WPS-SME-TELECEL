<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreJoinController extends Controller
{
    public function show(string $token): View
    {
        $store = Store::query()->where('invite_token', $token)->where('is_active', true)->firstOrFail();

        return view('stores.join', compact('store', 'token'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $store = Store::query()->where('invite_token', $token)->where('is_active', true)->firstOrFail();
        $user = $request->user();

        if ($user->ownedStores()->exists()) {
            return redirect()->route('dashboard')->withErrors(['join' => 'Store owners cannot join another store as a sub-agent.']);
        }

        if ($user->store_id && (int) $user->store_id !== (int) $store->id) {
            return redirect()->route('dashboard')->withErrors(['join' => 'Your account is already linked to another store.']);
        }

        if ((int) $user->store_id === (int) $store->id) {
            return redirect()->route('dashboard')->with('info', 'You are already a member of this store.');
        }

        $user->update([
            'store_id' => $store->id,
            'parent_user_id' => $store->user_id,
        ]);
        $user->syncRoles(['SubAgent']);

        return redirect()->route('dashboard')->with('success', 'You joined '.$store->name.'.');
    }
}
