<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Tests\Feature\Providers;

use Agenciafmd\Faqs\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('lists simple select options keyed by id with an empty option first', function (): void {
    $faq = Faq::factory()->create(['name' => 'Como funciona?']);

    expect(Faq::query()->toSimpleSelectOptions())->toBe([
        '' => '-',
        $faq->getKey() => 'Como funciona?',
    ]);
});

it('marks inactive records as disabled select options', function (): void {
    $faq = Faq::factory()->create(['name' => 'Inativa', 'is_active' => false]);

    expect(Faq::query()->toSelectOptions(disabled: true))->toBe([
        ['label' => '-', 'value' => '', 'disabled' => false],
        ['label' => 'Inativa', 'value' => $faq->getKey(), 'disabled' => true],
    ]);
});
