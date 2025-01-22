<?php

namespace Agenciafmd\Support\Providers;

use Agenciafmd\Support\Services\CacheService;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /*
     * Solução baseada no comentário do @wtoalabi
     * https://github.com/laracasts/matryoshka/issues/12#issuecomment-497722294
     *
     * Para cachear a model, é preciso adicionar
     * public function getCacheKey()
     * {
     *     return sprintf("%s/%s-%s",
     *         get_class($this),
     *         $this->getKey(),
     *         $this->updated_at->timestamp
     *     );
     * }
     * * */

    public function boot(): void
    {
        Blade::directive('cache', function ($expression) {
            return "<?php if (!\Agenciafmd\Support\Services\CacheService::setUp({$expression})) {?>";
        });
        Blade::directive('endcache', function () {
            return "<?php } echo \Agenciafmd\Support\Services\CacheService::tearDown() ?>";
        });

        if ($this->app->environment(['local']) && (config('cache.default') === 'redis') && !$this->app->runningInConsole()) {
            $cache = app(CacheRepository::class);
            if ($cache->supportsTags()) {
                $cache->tags('views')
                    ->flush();
            }
        }
    }

    public function register(): void
    {
        $this->app->singleton(CacheService::class);
    }
}
