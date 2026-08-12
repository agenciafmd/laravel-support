<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class HumanName implements ValidationRule
{
    /**
     * Executa a validação do campo nome contra padrões comuns de bots/hashes.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || empty(mb_trim($value))) {
            return;
        }

        $name = mb_trim($value);

        // 1. Rejeita 5 ou mais consoantes consecutivas (ex: BpFJWG, NtwIkS)
        if (preg_match('/[bcdfghjklmnpqrstvwxyz]{5,}/i', $name)) {
            $fail('O campo :attribute contém uma sequência de caracteres inválida.');

            return;
        }

        // 2. Rejeita alternâncias repetidas de maiúsculas/minúsculas no meio de uma palavra (ex: AzNtwIkS)
        // Permite marcas/sobrenomes legítimos (ex: McDonald, DeSilva), mas barra bagunça de bot
        if (preg_match('/([a-z]+[A-Z]){2,}/', $name)) {
            $fail('O campo :attribute possui uma formatação inválida.');

            return;
        }

        // 3. Analisa palavras longas (8+ chars) com taxa de vogais inferior a 20%
        $words = explode(' ', $name);
        foreach ($words as $word) {
            $length = mb_strlen($word);

            if ($length >= 8) {
                preg_match_all('/[aeiouáéíóúâêîôûãõàèìòùäëïöü]/ui', $word, $matches);
                $vowelCount = count($matches[0]);

                if (($vowelCount / $length) < 0.20) {
                    $fail('O campo :attribute não parece ser um nome válido.');

                    return;
                }
            }
        }
    }
}
