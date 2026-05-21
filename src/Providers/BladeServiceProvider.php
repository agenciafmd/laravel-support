<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class BladeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootBladeComponents();

        $this->bootBladeDirectives();

        $this->bootBladeComposers();

        $this->bootViews();

        $this->bootPublish();
    }

    public function register(): void
    {
        //
    }

    private function bootBladeComponents(): void
    {
        Blade::componentNamespace('Agenciafmd\\Support\\View\\Components', 'support');
    }

    private function bootBladeComposers(): void
    {
        //
    }

    private function bootBladeDirectives(): void
    {
        //
    }

    private function bootViews(): void
    {
        //        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'helper');
        //        $this->loadViewsFrom(base_path('resources/views/errors'), 'errors');
        //        $this->loadViewsFrom(__DIR__ . '/../../resources/mail', 'frontend-mail');
    }

    private function bootPublish(): void
    {
        //
    }
}
