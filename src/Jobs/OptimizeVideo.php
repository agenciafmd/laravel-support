<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Jobs;

use FFMpeg\Exception\ExecutableNotFoundException;
use FFMpeg\Format\Video\X264;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

/**
 * Converte o vídeo enviado para H.264/AAC, no tamanho exato informado, no disco em que ele foi salvo.
 * Amplia o vídeo até o menor lado atingir o alvo e corta o excedente do maior lado, centralizado,
 * como o `ImageUploadWithAutomaticallyResize` faz com as imagens. Com `mute`, remove a trilha de áudio. Arquivos que não são vídeo são ignorados,
 * e o vídeo fica como foi enviado quando o servidor não tem o ffmpeg/ffprobe.
 */
final class OptimizeVideo implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public int $tries = 1;

    public bool $failOnTimeout = true;

    public function __construct(
        public string $disk,
        public string $path,
        public int $width = 1440,
        public int $height = 810,
        public bool $mute = true,
    ) {
        $this->onQueue('low');
    }

    public function handle(): void
    {
        $disk = Storage::disk($this->disk);

        if (! $disk->exists($this->path) || ! str((string) $disk->mimeType($this->path))->startsWith('video/')) {
            return;
        }

        /* o libx264 só aceita dimensões pares */
        $width = $this->width - ($this->width % 2);
        $height = $this->height - ($this->height % 2);

        $temporaryPath = str($this->path)
            ->beforeLast('.')
            ->append('.optimizing.mp4')
            ->toString();

        $format = new X264;
        $format->setKiloBitrate(0);
        $format->setAdditionalParameters([
            '-crf', '28', '-preset', 'medium', '-pix_fmt', 'yuv420p', '-movflags', '+faststart',
            ...($this->mute ? ['-an'] : []),
        ]);

        try {
            $media = FFMpeg::fromDisk($this->disk)->open($this->path);
        } catch (ExecutableNotFoundException $exception) {
            logger()->warning('Video not optimized: ffmpeg/ffprobe not found on the server.', [
                'disk' => $this->disk,
                'path' => $this->path,
                'exception' => $exception->getMessage(),
            ]);

            return;
        }

        try {
            $media->getDriver()->addFilter(['-vf', "scale={$width}:{$height}:force_original_aspect_ratio=increase,crop={$width}:{$height},setsar=1"]);

            $exporter = $media->export();
            $exporter->toDisk($this->disk);
            $exporter->inFormat($format);
            $exporter->save($temporaryPath);

            $stream = $disk->readStream($temporaryPath);

            if (is_resource($stream)) {
                $disk->writeStream($this->path, $stream, ['visibility' => $disk->getVisibility($this->path)]);
            }
        } finally {
            $disk->delete($temporaryPath);

            FFMpeg::cleanupTemporaryFiles();
        }
    }
}
