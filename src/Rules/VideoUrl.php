<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Rules;

use Agenciafmd\Support\Helper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class VideoUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Helper::sanitizeVideo($value)) {
            return;
        }

        $fail(__('The :attribute must be a YouTube or Instagram reel url.'));
    }
}
