<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Tests\Feature\Providers;

use Agenciafmd\Admix\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class);

it('lists simple select options keyed by id with an empty option first', function (): void {
    $user = User::factory()->create(['name' => 'Maria Silva']);

    expect(User::query()->toSimpleSelectOptions())->toBe([
        '' => '-',
        $user->getKey() => 'Maria Silva',
    ]);
});

it('marks inactive records as disabled select options', function (): void {
    $user = User::factory()->create(['name' => 'João Souza', 'is_active' => false]);

    expect(User::query()->toSelectOptions(disabled: true))->toBe([
        ['label' => '-', 'value' => '', 'disabled' => false],
        ['label' => 'João Souza', 'value' => $user->getKey(), 'disabled' => true],
    ]);
});
