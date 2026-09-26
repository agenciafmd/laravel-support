<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Tests\Feature\Providers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds an acronym from the first letter of each word', function (): void {
    expect(Str::acronym('Agência F&MD Digital', '.'))->toBe('A.F.M.D.');
});

it('builds an empty acronym from an empty string', function (): void {
    expect(Str::acronym(''))->toBe('');
});

it('spells out each digit of an integer', function (): void {
    expect(Str::numbersToWords(209))->toBe('doiszeronove');
});

it('uses the given dictionary before the default one', function (): void {
    expect(Str::numbersToWords('a1', ['a' => 'A', '1' => 'one']))->toBe('Aone');
});

it('counts at least one minute of reading', function (): void {
    expect(Str::readDuration('duas palavras'))->toBe(1);
});

it('keeps the text when removing unprintable characters', function (): void {
    expect(Str::printable("linha\x00 um"))->toBe('linha um');
});
