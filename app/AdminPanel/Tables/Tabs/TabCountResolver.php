<?php

namespace App\AdminPanel\Tables\Tabs;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves per-tab record counts with versioned cache keys.
 * Only tabs with showCount() run a COUNT query (and only on cache miss).
 */
class TabCountResolver
{
    public function resolve(TabCollection $tabs, Builder $baseQuery, string $resource): array
    {
        $counts = [];
        $version = (int) Cache::get($this->versionKey($resource), 1);
        $ttl = (int) config('admin.table.tab_count_ttl', 300);

        foreach ($tabs->all() as $tab) {
            if (!$tab->shouldShowCount()) {
                continue;
            }

            $cacheKey = "tab_count:{$resource}:v{$version}:{$tab->getValue()}";

            $counts[$tab->getValue()] = Cache::remember($cacheKey, $ttl, function () use ($tab, $baseQuery) {
                return $tab->resolveCount(clone $baseQuery);
            });
        }

        return $counts;
    }

    /**
     * Invalidate cached counts for a resource (call on create/update/delete).
     */
    public function invalidate(string $resource): void
    {
        $key = $this->versionKey($resource);
        $version = (int) Cache::get($key, 1);
        Cache::forever($key, $version + 1);
    }

    private function versionKey(string $resource): string
    {
        return "tab_count_ver:{$resource}";
    }
}
