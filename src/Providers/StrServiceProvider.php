<?php

namespace Agenciafmd\Support\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;
use Illuminate\Support\Str;

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

    protected function loadStrMacros(): void
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

            return (int)max(1, $minutesToRead);
        });
    }

    protected function loadStringableMacros(): void
    {
        Stringable::macro('acronym', function (string $delimiter = '') {
            return new Stringable (Str::acronym($this->value, $delimiter));
        });

        Stringable::macro('readDuration', function () {
            return new Stringable (Str::readDuration($this->value));
        });
    }
}