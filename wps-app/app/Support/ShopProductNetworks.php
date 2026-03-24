<?php

namespace App\Support;

use Illuminate\Support\Collection;

final class ShopProductNetworks
{
    /**
     * @param  Collection<int, \App\Models\StoreBundlePrice>  $lines
     * @return array{0: \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, \App\Models\StoreBundlePrice>>, 1: \Illuminate\Support\Collection<int, string>}
     */
    public static function groupByNetwork(Collection $lines): array
    {
        $networkOrder = ['Telecel', 'MTN', 'Airtel Tigo'];
        $linesByNetwork = $lines->groupBy(fn ($p) => (string) ($p->bundleSubscription->network ?? 'Telecel'));
        $networksInShop = $linesByNetwork->keys()->sort(function (string $a, string $b) use ($networkOrder): int {
            $ia = array_search($a, $networkOrder, true);
            $ib = array_search($b, $networkOrder, true);
            $ia = $ia === false ? PHP_INT_MAX : $ia;
            $ib = $ib === false ? PHP_INT_MAX : $ib;
            if ($ia !== $ib) {
                return $ia <=> $ib;
            }

            return strcasecmp($a, $b);
        })->values();

        return [$linesByNetwork, $networksInShop];
    }
}
