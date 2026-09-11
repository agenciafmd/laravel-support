<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Traits;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

trait SeederSupport
{
    public string $storageUrl = 'https://fmd.ag/media/';

    /**
     * devemos sobrepor o `attributes` para adicionar novos atributos.
     *
     * @param  array<mixed, mixed>  $item
     * @return array<string, mixed>
     */
    protected function attributes(array $item): array
    {
        return [
            'id' => $this->integer($item, 'id'),
            'is_active' => true,
            'star' => $this->boolean($item, 'star'),
            'name' => $this->string($item, 'name'),
            'description' => str($this->string($item, 'description'))
                ->squish()
                ->replace('</p><p>', "\n")
                ->stripTags()
                ->toString(),
            'info' => $this->putOnStorage($this->string($item, 'info'), 'line/info/' . date('Y/m/d')),
            'image' => $this->putOnStorage($this->string($item, 'image'), 'line/image/' . date('Y/m/d')),
            'sort' => $this->integer($item, 'order'),
        ];
    }

    /**
     * The legacy rows of `database/data/lines.json`.
     *
     * @return list<array<mixed, mixed>>
     * @throws \Throwable
     */
    protected function rows(string $jsonFile = 'lines.json'): array
    {
        $contents = file_get_contents(database_path("data/{$jsonFile}"));

        throw_unless(is_string($contents), RuntimeException::class, 'Unable to read the legacy lines dump.');

        $rows = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        throw_unless(is_array($rows), RuntimeException::class, 'The legacy lines dump is not a valid JSON array.');

        return array_values(array_filter($rows, static fn (mixed $row): bool => is_array($row)));
    }

    /**
     * @param  array<mixed, mixed>  $item
     */
    protected function string(array $item, string $key): string
    {
        $value = $item[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param  array<mixed, mixed>  $item
     */
    protected function integer(array $item, string $key): int
    {
        $value = $item[$key] ?? null;

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @param  array<mixed, mixed>  $item
     */
    protected function boolean(array $item, string $key): bool
    {
        return (bool) ($item[$key] ?? false);
    }

    protected function putOnStorage(string $url, string $folder): ?string
    {
        if ($url === '') {
            return null;
        }

        $publicUrl = $this->storageUrl . $url;
        $fileInfo = pathinfo($url);
        $localPath = $fileInfo['dirname'];
        $fileName = $fileInfo['basename'];
        $storagePath = "media/{$folder}";

        if (Storage::exists("{$storagePath}/{$fileName}")) {
            return "{$storagePath}/{$fileName}";
        }

        $localFile = $this->downloadToLocal($publicUrl, "{$localPath}/{$fileName}");
        if (! $localFile) {
            return null;
        }

        return Storage::putFileAs($storagePath, new File($localFile), $fileName) ?: null;
    }

    protected function downloadToLocal(string $url, string $localPath): ?string
    {
        $fullLocalPath = database_path('resources/' . $localPath);

        if (file_exists($fullLocalPath)) {
            return $fullLocalPath;
        }

        $content = @file_get_contents($url);
        if ($content === false) {
            return null;
        }

        if (
            ! is_dir(dirname($fullLocalPath)) &&
            ! mkdir(dirname($fullLocalPath), 0775, true) &&
            ! is_dir(dirname($fullLocalPath))
        ) {
            return null;
        }

        file_put_contents($fullLocalPath, $content);

        return $fullLocalPath;
    }
}
