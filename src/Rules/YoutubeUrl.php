<?php

namespace Agenciafmd\Support\Rules;

use Agenciafmd\Support\Helper;
use Illuminate\Contracts\Validation\Rule;

/* TODO: refac https://laravel.com/docs/11.x/validation#using-rule-objects */

class YoutubeUrl implements Rule
{
    public function passes(mixed $attribute, mixed $value): bool
    {
        $validUrl = Helper::sanitizeYoutube($value);

        if ($validUrl) {
            return true;
        }

        return false;
    }

    public function message(): string
    {
        return __('validation.active_url');
    }
}
