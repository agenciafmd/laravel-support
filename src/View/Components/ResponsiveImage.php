<?php

declare(strict_types=1);

namespace Agenciafmd\Support\View\Components;

use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;
use Intervention\Image\Laravel\Facades\Image;

final class ResponsiveImage extends Component
{
    public array $image;

    public function __construct(
        public ?string $src,
        public int $quality = 80,
    ) {
        $this->image = $this->generate($src);
    }

    public function render(): string
    {
        return <<<'blade'
            @if(!empty($image['srcset']) && !empty($image['placeholder']))
                <img 
                    srcset="{{ $image['srcset'] }}, {{ $image['placeholder'] }} 32w" 
                    src="{{ $image['src'] }}"
                    {{ $attributes->merge([
                        'class' => 'img-fluid',
                        'loading' => 'lazy',
                        'decoding' => 'async',
                        'sizes' => '100vw',
                        'width' => '32',
                    ]) }}
                    onload="window.requestAnimationFrame(function(){if(!(size=getBoundingClientRect().width))return;onload=null;sizes=Math.ceil(size/window.innerWidth*100)+'vw';});"
                >
            @else
                {{-- Fallback de segurança caso os dados responsive falhem --}}
                <img src="{{ $image['src'] ?? $src }}" {{ $attributes->merge([
                        'class' => 'img-fluid',
                        'loading' => 'lazy',
                        'decoding' => 'async',
                        'sizes' => '100vw',
                        'width' => '32',
                    ]) }}>
            @endif
        blade;
    }

    private function generate(string $file, string $cacheDirectory = 'cache'): array
    {
        //        --max=85 --strip-all --all-progressive
        //        https://joelmale.com/blog/image-optimization-in-laravel-applications-optimize-user-images-easily
        $key = 'responsive-image-' . str($file)
            ->slug()
            ->toString();

        return cache()->rememberForever($key, function () use ($file, $cacheDirectory) {
            $hash = str(md5($file))->limit(10, '');
            $timestamp = now()->format('YmdHis');
            $directory = $cacheDirectory . '/' . pathinfo($file, PATHINFO_DIRNAME);
            $baseName = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $slugName = str($baseName)->slug() . "-{$hash}";
            $fileContent = Storage::get($file);
            $originalPath = "{$directory}/{$slugName}-original.{$extension}";
            if (Storage::disk('public')
                ->exists($originalPath) === false) {
                Storage::disk('public')
                    ->put($originalPath, $fileContent);
            }
            $originalPath = Storage::disk('public')
                ->url($originalPath);

            $sizes = $this->getSizes($fileContent);
            $srcsetArray = [];
            foreach ($sizes as $width) {
                $filename = "{$slugName}-{$width}w.{$extension}";
                $relativePath = "{$directory}/responsive/{$filename}";
                if (Storage::disk('public')
                    ->exists($relativePath) === false) {
                    $img = Image::read($fileContent);
                    $img->scale(width: $width);
                    Storage::disk('public')
                        ->put($relativePath, (string) $img->encodeByExtension($extension, quality: $this->quality));
                }
                $srcsetArray[] = Storage::disk('public')
                    ->url($relativePath) . " {$width}w";
            }
            $placeholderImg = Image::read($fileContent)
                ->scale(width: 32);
            $base64String = base64_encode((string) $placeholderImg->encodeByExtension($extension, quality: 20));
            $placeholderDataUri = "data:image/{$extension};base64,{$base64String}";

            return [
                'src' => Storage::disk('public')
                    ->url($originalPath),
                'srcset' => implode(', ', $srcsetArray),
                'placeholder' => $placeholderDataUri,
            ];
        });
    }

    private function getSizes(string $fileContent): array
    {
        $img = Image::read($fileContent);

        $sizes = [];
        $width = $img->width();
        do {
            $sizes[] = (int) $width;
            $width *= 0.75;
        } while ($width >= 200);

        return $sizes;
    }
}
