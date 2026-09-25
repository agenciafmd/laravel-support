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
        $locale = config('app.faker_locale');
        $locale = is_string($locale) && $locale !== '' ? $locale : 'en_US';

        $abstract = Generator::class . ':' . $locale;

        $this->app->afterResolving($abstract, function (Generator $instance): void {
            $instance->addProvider(new Provider($instance));
        });
    }
}
