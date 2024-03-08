<?php

namespace Agenciafmd\Support\Providers;

use Agenciafmd\Support\Helper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class StrServiceProvider extends ServiceProvider
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
        Str::macro('acronym', static function (string $string, string $delimiter = '') {
            if (empty($string)) {
                return '';
            }

            $acronym = '';
            foreach (preg_split('/[^\p{L}]+/u', $string) as $word) {
                if (!empty($word)) {
                    $first_letter = mb_substr($word, 0, 1);
                    $acronym .= $first_letter . $delimiter;
                }
            }

            return $acronym;
        });

        Str::macro('readDuration', static function (...$text) {
            $totalWords = str_word_count(implode(' ', $text));
            $minutesToRead = round($totalWords / 200);

            return (int) max(1, $minutesToRead);
        });

        Str::macro('localSquish', static function (string $string) {
            $string = preg_replace('~^[\s﻿]+|[\s﻿]+$~u', '', $string);
            $string = preg_replace('~(\s|\x{3164})+~u', ' ', $string);

            return trim($string);
        });

        Str::macro('printable', static function (string $string) {
            return preg_replace('/[[:^print:]]/', '', $string);
        });

        Str::macro('numbersToWords', static function (mixed $string, array $dictionary = []) {
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

            $string = str($string)
                ->localSquish()
                ->ascii()
                ->split('//');

            $convertedString = '';
            foreach ($string as $char) {
                $convertedString .= $dictionary[$char] ?? $char;
            }

            return $convertedString;
        });
    }

    private function loadStringableMacros(): void
    {
        Stringable::macro('acronym', function (string $delimiter = '') {
            return new Stringable (Str::acronym($this->value, $delimiter));
        });

        Stringable::macro('readDuration', function () {
            return new Stringable (Str::readDuration($this->value));
        });

        Stringable::macro('sanitizeName', function () {
            return new Stringable(Helper::sanitizeName($this->value));
        });

        Stringable::macro('localSquish', function () {
            return new Stringable(Str::localSquish($this->value));
        });

        Stringable::macro('printable', function () {
            return new Stringable(Str::printable($this->value));
        });

        Stringable::macro('numbersToWords', function (array $dictionary = []) {
            return new Stringable(Str::numbersToWords($this->value, $dictionary));
        });
    }
}