<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Cache Schema::hasTable / hasColumn results.
 * Calling information_schema on every request is expensive under load.
 */
class SchemaCache
{
    protected static int $ttl = 3600; // 1 hour

    public static function hasTable(string $table): bool
    {
        return (bool) Cache::remember('schema.table.'.$table, self::$ttl, function () use ($table) {
            try {
                return Schema::hasTable($table);
            } catch (\Throwable) {
                return false;
            }
        });
    }

    public static function hasColumn(string $table, string $column): bool
    {
        return (bool) Cache::remember('schema.col.'.$table.'.'.$column, self::$ttl, function () use ($table, $column) {
            try {
                return Schema::hasColumn($table, $column);
            } catch (\Throwable) {
                return false;
            }
        });
    }

    public static function flush(): void
    {
        // Best-effort; file/redis cache drivers support tags only on redis/memcached
        try {
            Cache::flush();
        } catch (\Throwable) {
        }
    }
}
