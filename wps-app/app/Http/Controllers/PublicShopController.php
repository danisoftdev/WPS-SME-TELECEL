<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Support\ShopProductNetworks;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicShopController extends Controller
{
    public function show(Store $store): View
    {
        if (! $store->is_active) {
            abort(404);
        }

        $store->load(['owner', 'bundlePrices.bundleSubscription']);

        $lines = $store->bundlePrices->filter(fn ($p) => $p->bundleSubscription && $p->bundleSubscription->is_active)->values();

        [$linesByNetwork, $networksInShop] = ShopProductNetworks::groupByNetwork($lines);

        $seoTitle = $store->name.' · Data bundles | WPS-SME';
        $desc = $store->description ? trim(strip_tags($store->description)) : '';
        $seoDescription = $desc !== ''
            ? Str::limit($desc, 160)
            : 'Buy mobile data bundles at '.$store->name.' — secure Paystack checkout on WPS-SME.';
        $seoImage = $store->seoOgImageUrl();
        $seoCanonicalUrl = route('shop.show', $store);
        $navShopStore = $store;

        return view('shop.show', compact(
            'store',
            'lines',
            'linesByNetwork',
            'networksInShop',
            'seoTitle',
            'seoDescription',
            'seoImage',
            'seoCanonicalUrl',
            'navShopStore'
        ));
    }
}
