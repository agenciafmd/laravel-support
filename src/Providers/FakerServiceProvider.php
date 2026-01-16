<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Providers;

use Agenciafmd\Support\Faker\Provider;
use Faker\Generator;
use Illuminate\Support\ServiceProvider;

final class FakerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $locale ??= app('config')->get('app.faker_locale') ?? 'en_US';
        $abstract = Generator::class . ':' . $locale;

        $this->app->afterResolving($abstract, function (Generator $instance) {
            $instance->addProvider(new Provider($instance));
        });
    }

    public function boot(): void
    {}
}
