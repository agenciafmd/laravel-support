<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Services;

use Exception;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Collection;

final class CacheService
{
    private static array $keys = [];

    public static function setUp($model)
    {
        ob_start();
        self::$keys[] = $key = self::normalizeKey($model);

        return self::has($key);
    }

    public static function tearDown()
    {
        $key = array_pop(self::$keys);
        $fragment = ob_get_clean();

        return self::put($key, $fragment);
    }

    public static function put($key, $fragment)
    {
        $key = self::normalizeCacheKey($key);
        $cache = app(Cache::class);

        return $cache
            ->tags('views')
            ->rememberForever($key, function () use ($fragment) {
                return $fragment;
            });
    }

    public static function has($key)
    {
        $key = self::normalizeCacheKey($key);
        $cache = app(Cache::class);

        return $cache
            ->tags('views')
            ->has($key);
    }

    private static function normalizeKey($item, $key = null)
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

    private static function normalizeCacheKey($key)
    {
        if (is_object($key) && method_exists($key, 'getCacheKey')) {
            return $key->getCacheKey();
        }

        return $key;
    }
}
