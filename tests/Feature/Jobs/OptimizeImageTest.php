<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Tests\Feature\Jobs;

use Agenciafmd\Support\Jobs\OptimizeImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Mockery\MockInterface;
use Spatie\ImageOptimizer\OptimizerChain;
use Tests\TestCase;

use function Pest\Laravel\mock;

pest()->extend(TestCase::class);

/**
 * PNG gerado pelo Imagick, já que o `UploadedFile::fake()->image()` depende do GD.
 */
function pngImage(int $width = 10, int $height = 10): string
{
    return (string) new ImageManager(new Driver)
        ->createImage($width, $height)
        ->encodeUsingFileExtension('png');
}

it('replaces the image with the optimized version when it is smaller', function (): void {
    Storage::fake('public');
    Storage::disk('public')->putFileAs('media', UploadedFile::fake()->createWithContent('photo.png', pngImage(200, 200)), 'photo.png');

    mock(OptimizerChain::class, static function (MockInterface $mock): void {
        $mock->shouldReceive('optimize')
            ->once()
            ->withArgs(static fn (string $path): bool => str_ends_with($path, '.png'))
            ->andReturnUsing(static fn (string $path): int|false => file_put_contents($path, 'optimized'));
    });

    OptimizeImage::dispatchSync('public', 'media/photo.png');

    expect(Storage::disk('public')->get('media/photo.png'))->toBe('optimized');
});

it('keeps the original image when the optimized version is not smaller', function (): void {
    Storage::fake('public');
    Storage::disk('public')->putFileAs('media', UploadedFile::fake()->createWithContent('photo.png', pngImage(10, 10)), 'photo.png');
    $original = Storage::disk('public')->get('media/photo.png');

    mock(OptimizerChain::class, static function (MockInterface $mock) use ($original): void {
        $mock->shouldReceive('optimize')
            ->once()
            ->andReturnUsing(static fn (string $path): int|false => file_put_contents($path, $original . 'bigger'));
    });

    OptimizeImage::dispatchSync('public', 'media/photo.png');

    expect(Storage::disk('public')->get('media/photo.png'))->toBe($original);
});

it('does not optimize files that are not images', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('media/document.txt', 'content');

    mock(OptimizerChain::class)->shouldNotReceive('optimize');

    OptimizeImage::dispatchSync('public', 'media/document.txt');

    expect(Storage::disk('public')->get('media/document.txt'))->toBe('content');
});

it('does nothing when the file no longer exists', function (): void {
    Storage::fake('public');

    mock(OptimizerChain::class)->shouldNotReceive('optimize');

    OptimizeImage::dispatchSync('public', 'media/missing.png');

    Storage::disk('public')->assertMissing('media/missing.png');
});
