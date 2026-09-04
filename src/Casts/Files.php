<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Normaliza o repeater de arquivos, gravando o tamanho em bytes junto de cada item.
 *
 * O tamanho é resolvido **uma vez, no save** — quando o upload já está no disco — em vez de a cada
 * leitura.
 *
 * @implements CastsAttributes<array<int, array<string, mixed>>, array<int, array<string, mixed>>|null>
 */
final class Files implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return array<int, array<string, mixed>>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (! is_string($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, string|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $files = $this->normalize(is_array($value) ? $value : []);

        return [
            $key => $files === []
                ? null
                : json_encode($files, JSON_THROW_ON_ERROR),
        ];
    }

    /**
     * Item sem `file` é descartado; arquivo que não está no disco fica com `size` nulo em vez de
     * estourar (import antigo, arquivo removido à mão).
     *
     * @param  array<int|string, mixed>  $files
     * @return array<int, array<string, mixed>>
     */
    private function normalize(array $files): array
    {
        return collect($files)
            ->filter(static fn (mixed $file): bool => is_array($file) && filled($file['file'] ?? null))
            ->map(static function (array $file): array {
                $path = (string) $file['file'];

                return [
                    'name' => $file['name'] ?? null,
                    'file' => $path,
                    'size' => rescue(static fn (): int => Storage::size($path), null, report: false),
                ];
            })
            ->values()
            ->all();
    }
}
