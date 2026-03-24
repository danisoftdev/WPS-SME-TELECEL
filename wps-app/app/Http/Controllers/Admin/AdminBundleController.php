<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BundleSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBundleController extends Controller
{
    private array $networks = ['Telecel', 'MTN', 'Airtel Tigo'];

    public function __construct()
    {
        $this->middleware('permission:manage_bundles');
    }

    public function index(): View
    {
        $bundles = BundleSubscription::orderByDesc('created_at')->paginate(20);
        return view('admin.bundles.index', compact('bundles'));
    }

    public function create(): View
    {
        return view('admin.bundles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'network' => 'required|string|in:' . implode(',', $this->networks),
            'total_data_gb' => 'required|numeric|min:0.01',
            'amount' => 'required|numeric|min:0',
            'max_beneficiaries' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);
        BundleSubscription::create([
            'name' => $request->name,
            'network' => $request->network,
            'total_data_gb' => $request->total_data_gb,
            'amount' => $request->amount,
            'max_beneficiaries' => $request->max_beneficiaries,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()->route('admin.bundles.index')->with('success', 'Bundle created.');
    }

    public function edit(BundleSubscription $bundle): View
    {
        return view('admin.bundles.edit', compact('bundle'));
    }

    public function update(Request $request, BundleSubscription $bundle): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'network' => 'required|string|in:' . implode(',', $this->networks),
            'total_data_gb' => 'required|numeric|min:0.01',
            'amount' => 'required|numeric|min:0',
            'max_beneficiaries' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);
        $bundle->update([
            'name' => $request->name,
            'network' => $request->network,
            'total_data_gb' => $request->total_data_gb,
            'amount' => $request->amount,
            'max_beneficiaries' => $request->max_beneficiaries,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()->route('admin.bundles.index')->with('success', 'Bundle updated.');
    }

    public function destroy(BundleSubscription $bundle): RedirectResponse
    {
        $bundle->delete();
        return redirect()->route('admin.bundles.index')->with('success', 'Bundle deleted.');
    }
}
