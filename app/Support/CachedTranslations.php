<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class CachedTranslations
{
    public static function get(string $namespace, string $locale): array
    {
        return Cache::remember(
            "inertia.translations.{$namespace}.{$locale}",
            now()->addDay(),
            fn () => trans($namespace, [], $locale),
        );
    }

    public static function forget(string $locale, string ...$namespaces): void
    {
        foreach ($namespaces as $namespace) {
            Cache::forget("inertia.translations.{$namespace}.{$locale}");
        }
    }
}
