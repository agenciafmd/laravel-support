<?php

namespace Agenciafmd\Support\Providers;

use Illuminate\Support\ServiceProvider;

class SupportServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->providers();
    }

    public function register()
    {
        //
    }

    protected function providers()
    {
        $this->app->register(CacheServiceProvider::class);
    }
}