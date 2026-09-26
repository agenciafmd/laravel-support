<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Tests\Feature\Rules;

use Agenciafmd\Support\Rules\CommaSeparatedEmails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('rejects a value that is not a string', function (): void {
    $validator = Validator::make(
        ['emails' => ['one@example.com']],
        ['emails' => [new CommaSeparatedEmails]],
    );

    expect($validator->errors()->get('emails'))->toBe(['O campo emails deve ser uma string.']);
});
