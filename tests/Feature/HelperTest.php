<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Tests\Feature;

use Agenciafmd\Support\Helper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('fills each placeholder of the mask with the next character', function (): void {
    expect(Helper::mask('12345678', '#####-###'))->toBe('12345-678');
});

it('keeps the remaining placeholders when the value is shorter than the mask', function (): void {
    expect(Helper::mask('123', '#####-###'))->toBe('123##-###');
});

it('discards extra characters instead of overwriting the start of the mask', function (): void {
    expect(Helper::mask('1234567890', '#####-###'))->toBe('12345-678');
});

it('formats numeric input the same way as string input', function (string $method, int $input, string $expected): void {
    expect(Helper::{$method}($input))->toBe($expected);
})->with([
    'phone' => ['sanitizePhone', 11987654321, '(11) 98765-4321'],
    'postal code' => ['sanitizePostalCode', 12345678, '12345-678'],
    'money' => ['formatMoney', 123456, 'R$ 1.234,56'],
]);

it('pads the hour and minute of a schedule with zeros', function (): void {
    expect(Helper::sanitizeSchedule('9:5'))->toBe('09:05');
});

it('keeps the last hour and minute of the day', function (): void {
    expect(Helper::sanitizeSchedule('23:59'))->toBe('23:59');
});

it('resets an hour or minute out of range to zero', function (string $schedule, string $expected): void {
    expect(Helper::sanitizeSchedule($schedule))->toBe($expected);
})->with([
    'hour' => ['24:30', '00:30'],
    'minute' => ['10:60', '10:00'],
]);

it('returns null for input that is not a scalar', function (string $method): void {
    expect(Helper::{$method}(['11987654321']))->toBeNull();
})->with([
    'phone' => 'sanitizePhone',
    'youtube id' => 'youtubeId',
    'schedule' => 'sanitizeSchedule',
]);

it('lists the states by abbreviation', function (): void {
    expect(Helper::states())
        ->toHaveCount(27)
        ->toHaveKey('SP', 'São Paulo');
});

it('lists the cities of a state keyed by their own name', function (): void {
    expect(Helper::cities('AC'))
        ->toHaveKey('Acrelândia', 'Acrelândia')
        ->not->toHaveKey('São Paulo');
});

it('returns no cities for an unknown state', function (): void {
    expect(Helper::cities('XX'))->toBe([]);
});

it('converts a decimal value to cents', function (): void {
    expect(Helper::floatToInt('12.34'))->toBe(1234);
});

it('rejects a value that is not numeric when converting to cents', function (): void {
    Helper::floatToInt('R$ 12,34');
})->throws(InvalidArgumentException::class);
