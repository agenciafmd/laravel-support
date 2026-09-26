<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Tests\Feature\Providers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

use function Pest\Laravel\get;

uses(TestCase::class, RefreshDatabase::class);

it('matches the current route name against a prefix', function (string|array $prefixes, string $expected): void {
    Route::get('/support-request-macro', fn (): string => request()->currentRouteNameStartsWith($prefixes) ? 'yes' : 'no')
        ->name('frontend.articles.index');

    get('/support-request-macro')->assertSeeText($expected);
})->with([
    'single matching prefix' => ['frontend.articles', 'yes'],
    'single other prefix' => ['admix', 'no'],
    'list with a matching prefix' => [['admix', 'frontend.'], 'yes'],
    'list without a matching prefix' => [['admix', 'api'], 'no'],
]);
