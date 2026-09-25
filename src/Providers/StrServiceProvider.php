<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Providers;

use Agenciafmd\Support\Helper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

final class StrServiceProvider extends ServiceProvider
{
    /*
     * Source:
     * https://github.com/koenhendriks/laravel-str-acronym/blob/main/src/StrServiceProvider.php#L15-L33
     * https://www.amitmerchant.com/estimated-reading-time-macro-in-laravel/
     * */

    public function boot(): void
    {
        $this->loadStrMacros();

        $this->loadStringableMacros();
    }

    public function register(): void
    {
        //
    }

    private function loadStrMacros(): void
    {
        Str::macro('acronym', function (string $string, string $delimiter = ''): string {
            if ($string === '') {
                return '';
            }

            $acronym = '';
            foreach (preg_split('/[^\p{L}]+/u', $string) ?: [] as $word) {
                if ($word !== '') {
                    $acronym .= mb_substr($word, 0, 1) . $delimiter;
                }
            }

            return $acronym;
        });

        Str::macro('readDuration', function (string ...$text): int {
            $totalWords = str_word_count(implode(' ', $text));
            $minutesToRead = round($totalWords / 200);

            return (int) max(1, $minutesToRead);
        });

        Str::macro('localSquish', function (string $string): string {
            $string = preg_replace('~^[\s﻿]+|[\s﻿]+$~u', '', $string) ?? $string;
            $string = preg_replace('~(\s|\x{3164})+~u', ' ', $string) ?? $string;

            return mb_trim($string);
        });

        Str::macro('printable', fn (string $string): string => preg_replace('/[[:^print:]]/', '', $string) ?? $string);

        Str::macro('numbersToWords', function (string|int $string, array $dictionary = []): string {
            $dictionary += [
                0 => 'zero',
                1 => 'um',
                2 => 'dois',
                3 => 'tres',
                4 => 'quatro',
                5 => 'cinco',
                6 => 'seis',
                7 => 'sete',
                8 => 'oito',
                9 => 'nove',
            ];

            return str((string) $string)
                ->localSquish()
                ->ascii()
                ->split('//')
                ->map(static function (string $character) use ($dictionary): string {
                    $word = $dictionary[$character] ?? $character;

                    return is_string($word) ? $word : $character;
                })
                ->implode('');
        });
    }

    private function loadStringableMacros(): void
    {
        Stringable::macro('acronym', fn (string $delimiter = ''): Stringable => new Stringable(Str::acronym($this->value, $delimiter)));

        Stringable::macro('readDuration', fn (): Stringable => new Stringable((string) Str::readDuration($this->value)));

        Stringable::macro('sanitizeName', fn (): Stringable => new Stringable(Helper::sanitizeName($this->value)));

        Stringable::macro('localSquish', fn (): Stringable => new Stringable(Str::localSquish($this->value)));

        Stringable::macro('printable', fn (): Stringable => new Stringable(Str::printable($this->value)));

        Stringable::macro('numbersToWords', fn (array $dictionary = []): Stringable => new Stringable(Str::numbersToWords($this->value, $dictionary)));
    }
}
