<?php

namespace Agenciafmd\Support\Providers;

use Illuminate\Support\ServiceProvider;

class SupportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootProviders();
    }

    public function register(): void
    {
        //
    }

    private function bootProviders(): void
    {
        $this->app->register(CacheServiceProvider::class);
        $this->app->register(EloquentServiceProvider::class);
        $this->app->register(RequestServiceProvider::class);
        $this->app->register(StrServiceProvider::class);
        $this->app->register(FakerServiceProvider::class);
    }
}
