<?php

namespace App\Rules;

use App\Models\BundleSubscription;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SellingPriceAboveBase implements ValidationRule
{
    public function __construct(private BundleSubscription $bundle) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $sell = number_format((float) $value, 2, '.', '');
        $base = number_format((float) $this->bundle->amount, 2, '.', '');

        if (function_exists('bccomp')) {
            if (bccomp($sell, $base, 2) <= 0) {
                $fail('The selling price must be strictly greater than the platform base price ('.$base.').');
            }

            return;
        }

        if ((float) $sell <= (float) $base) {
            $fail('The selling price must be strictly greater than the platform base price ('.$base.').');
        }
    }
}
