<?php

namespace Agenciafmd\Support\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

/* TODO: refac https://laravel.com/docs/11.x/validation#using-rule-objects */

class CommaSeparatedEmails implements Rule
{
    public function passes(mixed $attribute, mixed $value): bool
    {
        $value = str_replace([' ', ';'], ',', $value);
        $value = array_map('trim', explode(',', $value));
        $rules = [
            'email' => [
                'required',
                'email:rfc,dns',
            ],
        ];
        foreach ($value as $email) {
            $data = [
                'email' => $email,
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return __('validation.email');
    }
}
