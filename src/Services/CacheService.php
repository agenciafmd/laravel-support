<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Services;

use Exception;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Collection;

final class CacheService
{
    /**
     * @var array<int, string>
     */
    private static array $keys = [];

    public static function setUp(mixed $model): bool
    {
        ob_start();
        self::$keys[] = $key = self::normalizeKey($model);

        return self::has($key);
    }

    public static function tearDown(): string
    {
        $key = array_pop(self::$keys);
        throw_if($key === null, Exception::class, 'Cache tearDown called without a matching setUp.');

        $fragment = ob_get_clean();

        return self::put($key, $fragment === false ? '' : $fragment);
    }

    public static function put(mixed $key, string $fragment): string
    {
        $key = self::normalizeKey($key);
        $cache = resolve(Cache::class);

        return $cache
            ->tags('views')
            ->rememberForever($key, fn (): string => $fragment);
    }

    public static function has(mixed $key): bool
    {
        $key = self::normalizeKey($key);
        $cache = resolve(Cache::class);

        return $cache
            ->tags('views')
            ->has($key);
    }

    private static function normalizeKey(mixed $item): string
    {
        if (is_string($item)) {
            return $item;
        }

        if (is_object($item) && method_exists($item, 'getCacheKey')) {
            return (string) $item->getCacheKey();
        }

        if ($item instanceof Collection) {
            return md5($item->toJson());
        }

        throw new Exception('Could not determine an appropriate cache key.');
    }
}
