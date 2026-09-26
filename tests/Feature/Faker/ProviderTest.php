<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Tests\Feature\Faker;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('picks between one and the maximum distinct tags from the allowed list', function (): void {
    $allowed = ['Economia', 'Projetos', 'Tendências'];

    $tags = fake()->tags(max: 2, allowed: $allowed);

    expect($tags)
        ->toBeList()
        ->not->toBeEmpty()
        ->toHaveCount(count(array_unique($tags)))
        ->and(count($tags))->toBeLessThanOrEqual(2)
        ->and(array_diff($tags, $allowed))->toBe([]);
});

it('generates an eleven character youtube id', function (): void {
    expect(fake()->youtubeId())->toMatch('/^[0-9A-Za-z_-]{11}$/');
});
