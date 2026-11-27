<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\ImageOptimizer\OptimizerChain;

/**
 * Otimiza a imagem enviada no disco em que ela foi salva, com os binários disponíveis no servidor.
 * Arquivos que não são imagem são ignorados, e o original só é substituído quando o resultado fica menor.
 */
final class OptimizeImage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $disk,
        public string $path,
    ) {
        $this->onQueue('low');
    }

    public function handle(OptimizerChain $optimizerChain): void
    {
        $disk = Storage::disk($this->disk);

        if (! $disk->exists($this->path) || ! str((string) $disk->mimeType($this->path))->startsWith('image/')) {
            return;
        }

        /* o svgo só reconhece o arquivo pela extensão */
        $temporaryPath = str(sys_get_temp_dir())
            ->append('/admix-optimize-', Str::random(), '.', pathinfo($this->path, PATHINFO_EXTENSION))
            ->toString();

        try {
            file_put_contents($temporaryPath, $disk->get($this->path));

            $optimizerChain->optimize($temporaryPath);

            clearstatcache(true, $temporaryPath);

            if (filesize($temporaryPath) < $disk->size($this->path)) {
                $disk->put($this->path, (string) file_get_contents($temporaryPath), $disk->getVisibility($this->path));
            }
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }
}
