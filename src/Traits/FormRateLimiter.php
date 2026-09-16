<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Traits;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

trait FormRateLimiter
{
    public function withRateLimiter(): void
    {
        $throttleKey = str(self::class)->classBasename()->snake()->slug()->toString() . '-' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => __('validation.throttle', ['seconds' => RateLimiter::availableIn($throttleKey)]),
            ]);
        }

        RateLimiter::hit($throttleKey);
    }
}
