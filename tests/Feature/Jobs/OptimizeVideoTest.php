<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Tests\Feature\Jobs;

use Agenciafmd\Support\Jobs\OptimizeVideo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

pest()->extend(TestCase::class);

/**
 * Gera, com o ffmpeg do servidor, um vídeo de teste de 1 segundo, com áudio, no disco público.
 */
function storeVideo(string $path, int $width, int $height): void
{
    Storage::disk('public')->makeDirectory(dirname($path));

    Process::run([
        'ffmpeg', '-y', '-loglevel', 'error',
        '-f', 'lavfi', '-i', "testsrc=size={$width}x{$height}:rate=25:duration=1",
        '-f', 'lavfi', '-i', 'sine=frequency=440:duration=1',
        '-c:v', 'mpeg4', '-c:a', 'aac', '-shortest', Storage::disk('public')->path($path),
    ])->throw();
}

/**
 * Lê o stream de vídeo com o ffprobe direto, já que o FFProbe do php-ffmpeg guarda cache por caminho.
 *
 * @return array{codec_name: string, width: int, height: int}
 */
function videoStream(string $path): array
{
    $output = Process::run([
        'ffprobe', '-v', 'error', '-select_streams', 'v:0',
        '-show_entries', 'stream=codec_name,width,height', '-of', 'json',
        Storage::disk('public')->path($path),
    ])->throw()->output();

    /** @var array{streams: list<array{codec_name: string, width: int, height: int}>} $probe */
    $probe = json_decode($output, true, flags: JSON_THROW_ON_ERROR);

    return $probe['streams'][0];
}

/**
 * Quantidade de trilhas de áudio do arquivo, lida com o ffprobe direto.
 */
function audioStreamCount(string $path): int
{
    $output = Process::run([
        'ffprobe', '-v', 'error', '-select_streams', 'a',
        '-show_entries', 'stream=index', '-of', 'csv=p=0',
        Storage::disk('public')->path($path),
    ])->throw()->output();

    return count(array_filter(explode("\n", mb_trim($output))));
}

it('converts the video to H.264 with the exact dimensions, cropping the excess', function (): void {
    Storage::fake('public');
    storeVideo('media/video.mp4', 640, 360);

    dispatch_sync(new OptimizeVideo('public', 'media/video.mp4', 200, 200));

    expect(videoStream('media/video.mp4'))->toBe(['codec_name' => 'h264', 'width' => 200, 'height' => 200])
        ->and(Storage::disk('public')->files('media'))->toBe(['media/video.mp4']);
});

it('removes the audio track by default', function (): void {
    Storage::fake('public');
    storeVideo('media/video.mp4', 640, 360);

    dispatch_sync(new OptimizeVideo('public', 'media/video.mp4', 200, 200));

    expect(audioStreamCount('media/video.mp4'))->toBe(0);
});

it('keeps the audio track when it is not muted', function (): void {
    Storage::fake('public');
    storeVideo('media/video.mp4', 640, 360);

    dispatch_sync(new OptimizeVideo('public', 'media/video.mp4', 200, 200, mute: false));

    expect(audioStreamCount('media/video.mp4'))->toBe(1);
});

it('rounds odd dimensions down to even numbers', function (): void {
    Storage::fake('public');
    storeVideo('media/video.mp4', 640, 360);

    dispatch_sync(new OptimizeVideo('public', 'media/video.mp4', 201, 101));

    expect(videoStream('media/video.mp4'))->toMatchArray(['width' => 200, 'height' => 100]);
});

it('does not convert files that are not videos', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('media/document.txt', 'content');

    dispatch_sync(new OptimizeVideo('public', 'media/document.txt', 200, 200));

    expect(Storage::disk('public')->get('media/document.txt'))->toBe('content');
});

it('does nothing when the file no longer exists', function (): void {
    Storage::fake('public');

    dispatch_sync(new OptimizeVideo('public', 'media/missing.mp4', 200, 200));

    Storage::disk('public')->assertMissing('media/missing.mp4');
});

it('keeps the video as uploaded and logs a warning when ffmpeg is not installed', function (): void {
    Storage::fake('public');
    storeVideo('media/video.mp4', 640, 360);
    $original = Storage::disk('public')->get('media/video.mp4');
    config()->set('laravel-ffmpeg.ffmpeg.binaries', '/nonexistent/ffmpeg');
    config()->set('laravel-ffmpeg.ffprobe.binaries', '/nonexistent/ffprobe');
    Log::spy();

    dispatch_sync(new OptimizeVideo('public', 'media/video.mp4', 200, 200));

    expect(Storage::disk('public')->get('media/video.mp4'))->toBe($original)
        ->and(Storage::disk('public')->files('media'))->toBe(['media/video.mp4']);
    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(static fn (string $message, array $context): bool => $context['path'] === 'media/video.mp4');
});
