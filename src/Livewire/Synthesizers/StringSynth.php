<?php

namespace Agenciafmd\Support\Livewire\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

// This synth exists solely to capture empty strings being set to string properties...
class StringSynth extends Synth
{
    public static $key = 'string';

    public static function match($target): bool
    {
        return false;
    }

    public static function matchByType($type): bool
    {
        return $type === 'string';
    }

    public static function hydrateFromType(mixed $type, mixed $value): ?string
    {
        $value = str()
            ->of($value)
            ->trim()
            ->toString();

        if ($value === '' || $value === null) {
            return null;
        }

        return (string) $value;
    }
}
