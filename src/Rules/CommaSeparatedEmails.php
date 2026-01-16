<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

final class CommaSeparatedEmails implements ValidationRule
{
    private const EMAIL_RULES = [
        'email' => [
            'required',
            'email:rfc,dns',
        ],
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $emails = $this->parseEmails($value);

        foreach ($emails as $email) {
            $validator = Validator::make(['email' => $email], self::EMAIL_RULES);

            if ($validator->fails()) {
                $fail(__('validation.email'));
            }
        }
    }

    private function parseEmails(string $value): array
    {
        $normalized = str_replace([' ', ';'], ',', $value);

        return array_filter(array_map('trim', explode(',', $normalized)));
    }
}
