<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $plans = $request->user()
            ->resellerPlans()
            ->where('is_active', true)
            ->with('userSubscription.bundleSubscription')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'data_size_gb' => (float) $p->data_size_gb,
                'price' => (float) $p->price,
                'available_units' => $p->available_units,
                'network' => $p->userSubscription->bundleSubscription->network ?? 'Telecel',
                'subscription_name' => $p->userSubscription->bundleSubscription->name ?? null,
            ]);
        return response()->json(['data' => $plans]);
    }

    public function subscriptions(Request $request): JsonResponse
    {
        $subs = $request->user()
            ->userSubscriptions()
            ->with('bundleSubscription')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'bundle_name' => $s->bundleSubscription->name ?? null,
                'network' => $s->bundleSubscription->network ?? 'Telecel',
                'balance_gb' => (float) $s->balance_gb,
                'beneficiaries_count' => is_array($s->beneficiaries) ? count($s->beneficiaries) : 0,
            ]);
        return response()->json(['data' => $subs]);
    }
}
