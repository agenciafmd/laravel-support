<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\OptimizerChainFactory;

final class SupportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootProviders();
    }

    public function register(): void
    {
        $this->registerBindings();
    }

    private function bootProviders(): void
    {
        $this->app->register(CacheServiceProvider::class);
        $this->app->register(BladeServiceProvider::class);
        $this->app->register(EloquentServiceProvider::class);
        $this->app->register(RequestServiceProvider::class);
        $this->app->register(StrServiceProvider::class);
        $this->app->register(FakerServiceProvider::class);
    }

    private function registerBindings(): void
    {
        $this->app->bind(OptimizerChain::class, static fn (): OptimizerChain => OptimizerChainFactory::create());
    }
}
