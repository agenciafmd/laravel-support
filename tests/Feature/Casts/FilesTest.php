<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Tests\Feature\Casts;

use Agenciafmd\Support\Casts\Files;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('stores each file with its size in bytes', function (): void {
    Storage::fake();
    Storage::put('files/manual.pdf', 'pdf-content');

    $stored = (new Files)->set(new User, 'files', [
        ['name' => 'Manual', 'file' => 'files/manual.pdf'],
    ], []);

    expect(json_decode((string) $stored['files'], true))->toBe([
        ['name' => 'Manual', 'file' => 'files/manual.pdf', 'size' => 11],
    ]);
});

it('stores a null size when the file is not on disk', function (): void {
    Storage::fake();

    $stored = (new Files)->set(new User, 'files', [
        ['name' => 'Missing', 'file' => 'files/missing.pdf'],
    ], []);

    expect(json_decode((string) $stored['files'], true))->toBe([
        ['name' => 'Missing', 'file' => 'files/missing.pdf', 'size' => null],
    ]);
});

it('stores null when no item has a file', function (): void {
    Storage::fake();

    $stored = (new Files)->set(new User, 'files', [
        ['name' => 'No file'],
        ['name' => 'Blank file', 'file' => '   '],
        'not an item',
    ], []);

    expect($stored)->toBe(['files' => null]);
});

it('reads only the items that are arrays', function (): void {
    $value = json_encode([
        ['name' => 'Manual', 'file' => 'files/manual.pdf', 'size' => 11],
        'not an item',
    ]);

    $files = (new Files)->get(new User, 'files', $value, []);

    expect($files)->toBe([
        ['name' => 'Manual', 'file' => 'files/manual.pdf', 'size' => 11],
    ]);
});

it('reads an empty list when the stored value is not valid json', function (): void {
    expect((new Files)->get(new User, 'files', 'not-json', []))->toBe([]);
});
