<?php

namespace Agenciafmd\Support\Providers;

use Illuminate\Support\ServiceProvider;

class SupportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->providers();
    }

    public function register(): void
    {
        //
    }

    private function providers(): void
    {
        $this->app->register(CacheServiceProvider::class);
        $this->app->register(RequestServiceProvider::class);
        $this->app->register(StrServiceProvider::class);
    }
}
