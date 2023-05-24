<?php

namespace Agenciafmd\Support\Providers;

use Agenciafmd\Support\Helper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;

class SupportServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->providers();

        $this->stringableMacros();
    }

    public function register()
    {
        //
    }

    protected function providers()
    {
        $this->app->register(CacheServiceProvider::class);
        $this->app->register(StrServiceProvider::class);
    }

    protected function stringableMacros()
    {
        Stringable::macro('sanitizeName', function () {
            return new Stringable(Helper::sanitizeName($this->value));
        });

        Stringable::macro('localSquish', function () {
            return new Stringable(preg_replace('~(\s|\x{3164})+~u', ' ', preg_replace('~^[\s﻿]+|[\s﻿]+$~u', '', $this->value)));
        });

        Stringable::macro('numbersToWords', function () {
            return new Stringable(Helper::numbersToWords($this->value));
        });
    }
}