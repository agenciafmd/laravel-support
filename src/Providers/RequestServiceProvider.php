<?php

namespace Agenciafmd\Support\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRequestMacros();
    }

    public function register(): void
    {
        //
    }

    private function loadRequestMacros(): void
    {
        Request::macro('currentRouteNameStartsWith', function ($routeNames) {
            $routeNames = Arr::wrap($routeNames);

            return str(request()
                ?->route()
                ?->getName())
                ->startsWith($routeNames);
        });
    }
}