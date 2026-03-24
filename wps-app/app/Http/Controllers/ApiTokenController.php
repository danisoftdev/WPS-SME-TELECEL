<?php

namespace App\Http\Controllers;

use App\Support\ApiTokenAbilities;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:access_api']);
    }

    public function index(Request $request): View
    {
        $tokens = $request->user()->tokens()->orderByDesc('created_at')->get();

        return view('api_tokens.index', compact('tokens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $user = $request->user();
        $plain = $user->createToken(
            $request->name,
            ApiTokenAbilities::forUser($user)
        )->plainTextToken;

        return redirect()
            ->route('api-tokens.index')
            ->with('api_token_plain', $plain)
            ->with('api_token_name', $request->name);
    }

    public function destroy(Request $request, string $token): RedirectResponse
    {
        $row = $request->user()->tokens()->whereKey((int) $token)->first();
        if (! $row) {
            abort(404);
        }
        $row->delete();

        return redirect()->route('api-tokens.index')->with('success', 'API token revoked.');
    }
}
