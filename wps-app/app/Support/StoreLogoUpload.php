<?php

namespace App\Support;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class StoreLogoUpload
{
    public const VALIDATION_RULE = 'nullable|image|mimes:jpeg,png,webp,gif|max:2048';

    public static function deleteIfPresent(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * After request validates `logo` with VALIDATION_RULE. Deletes previous file when replacing.
     */
    public static function persistFromRequest(Request $request, ?Store $existing = null): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        if ($existing?->logo_path) {
            Storage::disk('public')->delete($existing->logo_path);
        }

        return $request->file('logo')->store('store-logos', 'public');
    }
}
