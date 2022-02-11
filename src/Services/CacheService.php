<?php

namespace Agenciafmd\Support\Services;

use Exception;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Collection;

class CacheService
{
    private static array $keys = [];

    public static function setUp($model)
    {
        ob_start();
        static::$keys[] = $key = self::normalizeKey($model);

        return static::has($key);
    }

    public static function tearDown()
    {
        $key = array_pop(static::$keys);
        $fragment = ob_get_clean();

        return static::put($key, $fragment);
    }

    protected static function normalizeKey($item, $key = null)
    {
        if (is_string($item) || is_string($key)) {
            return is_string($item) ? $item : $key;
        }

        if (is_object($item) && method_exists($item, 'getCacheKey')) {
            return $item->getCacheKey();
        }

        if ($item instanceof Collection) {
            return md5($item);
        }

        return new Exception('Could not determine an appropriate cache key.');
    }

    public static function put($key, $fragment)
    {
        $key = static::normalizeCacheKey($key);
        $cache = app(Cache::class);
        return $cache
            ->tags('views')
            ->rememberForever($key, function () use ($fragment) {
                return $fragment;
            });
    }

    public static function has($key)
    {
        $key = static::normalizeCacheKey($key);
        $cache = app(Cache::class);
        return $cache
            ->tags('views')
            ->has($key);
    }

    private static function normalizeCacheKey($key)
    {
        if (is_object($key) && method_exists($key, 'getCacheKey')) {
            return $key->getCacheKey();
        }

        return $key;
    }
}