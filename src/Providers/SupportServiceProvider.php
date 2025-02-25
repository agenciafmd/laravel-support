<?php

namespace Agenciafmd\Support\Providers;

use Agenciafmd\Support\Livewire\Synthesizers\StringSynth;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class SupportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootProviders();
        $this->loadLivewireSynth();
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
    }

    private function loadLivewireSynth(): void
    {
        Livewire::propertySynthesizer(StringSynth::class);
    }
}
