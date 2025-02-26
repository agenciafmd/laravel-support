<?php

namespace Agenciafmd\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use voku\helper\ASCII;

class Helper
{
    /**
     * Ofusca o numero de telefone ignorando as posições passadas
     *
     * @param string $string
     * @param int $initialChars
     * @param int $finalChars
     * @return string
     */
    public static function secretPhone(string $string, int $initialChars = 4, int $finalChars = 2): string
    {
        $string = Helper::onlyNumbers($string);

        if (!$string) {
            return '';
        }

        $initial = substr($string, 0, $initialChars);
        $final = substr($string, -1 * $finalChars);
        $length = strlen($string) - $finalChars;

        return Helper::mask(str_pad($initial, $length, '*') . $final, '(##) #####-####');
    }

    /**
     * Retorna somente os numeros da string
     *
     * @param string $string
     * @return string
     */
    public static function onlyNumbers(string $string): string
    {
        return preg_replace('/[^0-9]/', '', $string);
    }

    /**
     * Retorna somente os números e letras da string
     *
     * @param string $string
     * @return string
     */
    public static function onlyAlphanumeric(string $string): string
    {
        return preg_replace('/[[:^alnum:]]/', '', $string);
    }

    /**
     * Formata a string a partir da mascara
     *
     * @param string $string
     * @param string $mask
     * @return string
     */
    public static function mask(string $string, string $mask): string
    {
        $string = str_replace(' ', '', $string);
        $length = strlen($string);

        for ($i = 0; $i < $length; $i++) {
            $mask[strpos($mask, "#")] = $string[$i];
        }

        return $mask;
    }

    /**
     * Checa se o telefone é valido e retorna o mesmo formatado
     *
     * @param mixed $phone
     * @return string | null
     */
    public static function sanitizePhone($phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $numericPhone = Helper::onlyNumbers($phone);

        if (Str::length($numericPhone) === 10) {
            return Helper::mask($numericPhone, '(##) ####-####');
        }

        if (Str::length($numericPhone) === 11) {
            return Helper::mask($numericPhone, '(##) #####-####');
        }

        return null;
    }

    /**
     * Checa se o cpf é valido e retorna o mesmo formatado
     *
     * @param mixed $cpf
     * @return string | null
     */
    public static function sanitizeCpf($cpf): ?string
    {
        if (!$cpf) {
            return null;
        }

        $validator = app('validator')->make(['cpf' => $cpf], ['cpf' => 'cpf']);
        if ($validator->fails()) {
            return null;
        }

        $cpf = Helper::onlyNumbers($cpf);

        return Helper::mask($cpf, '###.###.###-##');
    }

    /**
     * Checa se o cnpj é valido e retorna o mesmo formatado
     *
     * @param mixed $cnpj
     * @return string | null
     */
    public static function sanitizeCnpj($cnpj): ?string
    {
        if (!$cnpj) {
            return null;
        }

        $validator = app('validator')->make(['cnpj' => $cnpj], ['cnpj' => 'cnpj']);
        if ($validator->fails()) {
            return null;
        }

        $cnpj = Helper::onlyNumbers($cnpj);

        return Helper::mask($cnpj, '##.###.###/####-##');
    }

    /**
     * Normaliza o RG e retorna o mesmo formatado
     *
     * @param mixed $rg
     * @return string | null
     */
    public static function sanitizeRg($rg): ?string
    {
        if (!$rg) {
            return null;
        }

        $rg = Helper::onlyAlphanumeric($rg);

        $digit = substr($rg, -1);
        $body = Helper::onlyNumbers(substr($rg, 0, -1));
        $pieces = str_split(strrev($body), 3);
        $body = strrev(implode('.', $pieces));

        return Str::upper("{$body}-{$digit}");
    }

    /**
     * Checa se o email é valido e retorna o mesmo
     *
     * @param mixed $email
     * @return string | null
     */
    public static function sanitizeEmail($email): ?string
    {
        if (!$email) {
            return null;
        }

        $email = ASCII::to_ascii((string)$email, 'en');

        $validator = app('validator')->make(['email' => $email], ['email' => 'email:rfc,dns']);
        if ($validator->fails()) {
            return null;
        }

        return Str::of($email)
            ->lower()
            ->trim();
    }

    /**
     * Normaliza os nomes de pessoas de UPPER_CASE para ucfirst
     *
     * @param string $name
     * @return string
     */
    public static function sanitizeName(string $name): string
    {
        $search = ["De ", "Do ", "Dos ", "Da ", "Das "];
        $replace = ["de ", "do ", "dos ", "da ", "das "];

        $name = ucwords(Str::of($name)
            ->lower()
            ->trim());

        return str_replace($search, $replace, $name);
    }

    /**
     * Checa se o CEP é valido e retorna o mesmo formatado
     *
     * @param mixed $postalCode
     * @return string | null
     */
    public static function sanitizePostalCode($postalCode): ?string
    {
        if (!$postalCode) {
            return null;
        }

        $postalCode = Helper::onlyNumbers($postalCode);

        return Helper::mask($postalCode, '#####-###');
    }

    /**
     * Checa se o horário é valido e retorna o mesmo formatado
     *
     * @param mixed $schedule
     * @return string | null
     */
    public static function sanitizeSchedule($schedule): ?string
    {
        $schedule = trim($schedule);

        if (!$schedule) {
            return null;
        }

        if (Str::length($schedule) > 5) {
            return null;
        }

        if (!Str::contains($schedule, ':')) {
            return null;
        }

        [$hour, $minute] = explode(':', $schedule);

        $hour = ($hour < 23) ? $hour : '00';
        $minute = ($minute < 59) ? $minute : '00';

        $hour = Str::padLeft($hour, 2, '0');
        $minute = Str::padLeft($minute, 2, '0');

        return "{$hour}:{$minute}";
    }

    /**
     * Normaliza o link do youtube para o formato de compartilhar
     */
    public static function sanitizeYoutube(mixed $url): ?string
    {
        $id = Helper::youtubeId($url);

        if (!$id) {
            return null;
        }

        return "https://youtu.be/{$id}";
    }

    /**
     * Retorna o id do youtube a partir de qualquer link
     */
    public static function youtubeId(mixed $url): ?string
    {
        if (!$url) {
            return null;
        }

        if (!Str::of($url)
            ->contains(['youtu.be', 'youtube.com'])) {
            return null;
        }

        $id = Str::of($url)
            ->replace('/www.', '/')
            ->replace([
                'https://youtu.be/',
                'https://youtube.com/watch?v=',
                'https://youtube.com/embed/',
                'https://youtube.com/shorts/',
            ], '')
            ->before('?si=')
            ->before('?t=')
            ->before('&t=');

        if (!$id) {
            return null;
        }

        return $id;
    }

    /**
     * Retorna somente caracteres que podem ser visualizados
     *
     * @param string $string
     * @return string | null
     */
    public static function printable(string $string): ?string
    {
        if (!$string) {
            return null;
        }

        return preg_replace('/[[:^print:]]/', '', $string);
    }

    /**
     * Formata o retorno de sucesso para json normalizado
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function success($data, string $message = 'Item encontrado', int $code = 200): JsonResponse
    {
        if ($data instanceof Collection) {
            $count = $data->count();
            $message = $count . (($count <= 1) ? ' item encontrado' : ' itens encontrados');
        }

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Formata o retorno de falha para json normalizado
     *
     * @param array $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function error(array $data = [], string $message = 'Item não encontrado', int $code = 404): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Formata inteiro para moeda
     *
     * @param mixed $value
     * @param string $currency
     * @return string | null
     */
    public static function formatMoney($value, string $currency = 'R$ '): ?string
    {
        if (!$value) {
            return null;
        }

        $value = Helper::onlyNumbers($value);

        $decimal = substr($value, -2);
        $body = substr($value, 0, -2);
        $pieces = str_split(strrev($body), 3);
        $body = strrev(implode('.', $pieces));

        return "{$currency}{$body},{$decimal}";
    }

    public static function httpStripQueryParam(string $param, string|null $value = null, string|null $url = null): string
    {
        if (!$url) {
            $url = request()->fullUrl();
        }

        $baseUrl = strtok($url, '?');
        $pieces = parse_url($url);

        $query = [];
        if (isset($pieces['query']) && $pieces['query']) {
            parse_str($pieces['query'], $query);

            if (is_array($query[$param]) && $value) {
                if (($key = array_search($value, $query[$param])) !== false) {
                    unset($query[$param][$key]);
                }
            } else {
                unset($query[$param]);
            }
        }

        $newQuery = http_build_query($query);

        return $baseUrl . (($newQuery) ? '?' . $newQuery : '');
    }

    /**
     * Converte os numeros de uma string para palavras
     *
     * @param mixed $value
     * @return string
     */

    public static function numbersToWords(mixed $value): string
    {
        $numbersChars = [
            '0' => 'zero',
            '1' => 'um',
            '2' => 'dois',
            '3' => 'tres',
            '4' => 'quatro',
            '5' => 'cinco',
            '6' => 'seis',
            '7' => 'sete',
            '8' => 'oito',
            '9' => 'nove',
        ];

        $name = Str::of($value)
            ->localSquish()
            ->ascii()
            ->split('//');

        $convertedString = '';
        foreach ($name as $char) {
            $convertedString .= $numbersChars[$char] ?? $char;
        }

        return $convertedString;
    }
}
