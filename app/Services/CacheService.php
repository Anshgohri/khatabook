<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * A thin wrapper around Laravel's Cache facade that:
 *  - Automatically scopes cache keys per user (so User A never sees User B's data)
 *  - Uses a consistent TTL
 *  - Provides a single invalidate() method to bust all cached data for a model
 */
class CacheService
{
    /** Default TTL for dashboard/report stats in seconds (5 minutes) */
    public const STATS_TTL = 300;

    /** TTL for reference data that rarely changes (products, categories, roles) */
    public const REFERENCE_TTL = 1800; // 30 min

    /** TTL for list data (paginated tables with filters) — kept short */
    public const LIST_TTL = 60; // 1 min

    /**
     * Remember a value scoped to a specific user.
     *
     * @param  string  $key    cache key suffix (e.g. 'dashboard.sales_today')
     * @param  int     $userId the user whose data this belongs to
     * @param  int     $ttl    seconds
     * @param  callable $callback the query to run on cache miss
     */
    public static function rememberForUser(string $key, int $userId, int $ttl, callable $callback): mixed
    {
        return Cache::remember("user:{$userId}:{$key}", $ttl, $callback);
    }

    /**
     * Remember a value globally (not scoped to a user — e.g. product lists).
     */
    public static function rememberGlobal(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember("global:{$key}", $ttl, $callback);
    }

    /**
     * Invalidate all cached data for a given user.
     * Call this after any write operation (sale/expense created, etc.)
     */
    public static function invalidateUser(int $userId): void
    {
        // Tag-based invalidation (works with Redis)
        if (self::supportsTags()) {
            Cache::tags(["user:{$userId}"])->flush();

            return;
        }

        // Fallback: flush individual known keys for this user
        $keys = [
            "user:{$userId}:dashboard.sales_today",
            "user:{$userId}:dashboard.sales_month",
            "user:{$userId}:dashboard.sales_year",
            "user:{$userId}:dashboard.expenses_month",
            "user:{$userId}:dashboard.sales_trend",
            "user:{$userId}:dashboard.expense_breakdown",
            "user:{$userId}:dashboard.top_items",
            "user:{$userId}:financiers.payments_week",
            "user:{$userId}:financiers.payments_month",
            "user:{$userId}:financiers.payments_year",
            "user:{$userId}:financiers.outstanding",
            "user:{$userId}:reports.total_sales",
            "user:{$userId}:reports.total_expenses",
            "user:{$userId}:reports.sales_by_item",
            "user:{$userId}:reports.expenses_by_category",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Invalidate shared/global reference caches (product list, categories, etc.)
     */
    public static function invalidateGlobal(string ...$keys): void
    {
        foreach ($keys as $key) {
            Cache::forget("global:{$key}");
        }
    }

    /**
     * Check whether the configured cache driver supports tags (Redis/Memcached do; DB/file do not).
     */
    public static function supportsTags(): bool
    {
        return in_array(config('cache.default'), ['redis', 'memcached'], true);
    }
}
