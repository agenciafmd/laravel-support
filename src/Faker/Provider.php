<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Faker;

use Faker\Provider\Base;
use InvalidArgumentException;

final class Provider extends Base
{
    /*
     * https://github.com/aalaap/faker-youtube/blob/master/src/Youtube.php
     * */

    public function youtubeId(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . 'abcdefghijklmnopqrstuvwxyz_-';

        $id = mb_substr(self::shuffle($characters), 0, 11);

        return $this->generator->parse($id);
    }

    public function youtubeUri(): string
    {
        return 'https://www.youtube.com/watch?v=' . $this->youtubeId();
    }

    public function youtubeShortUri(): string
    {
        return 'https://youtu.be/' . $this->youtubeId();
    }

    public function youtubeEmbedUri(): string
    {
        return 'https://www.youtube.com/embed/' . $this->youtubeId();
    }

    public function youtubeEmbedCode(): string
    {
        return '<iframe width="560" height="315" src="' . $this->youtubeEmbedUri()
            . '" frameborder="0" gesture="media" allow="encrypted-media"'
            . ' allowfullscreen></iframe>';
    }

    public function youtubeChannelUri(): string
    {
        return sprintf('https://www.youtube.com/%s/%s',
            self::randomElement(['channel', 'user']),
            self::regexify(sprintf('[a-zA-Z0-9\-]{1,%s}', self::numberBetween(1, 20)))
        );
    }

    public function youtubeRandomUri(): string
    {
        return match (self::numberBetween(1, 3)) {
            1 => $this->youtubeShortUri(),
            2 => $this->youtubeEmbedUri(),
            default => $this->youtubeUri(),
        };
    }

    public function localImage($ratio = '16x9', $sourceDir = null): string
    {
        $ratio = str_replace(':', 'x', $ratio);
        if (! in_array($ratio, ['1x1', '4x3', '16x9', '21x9'], true)) {
            throw new InvalidArgumentException(sprintf('Invalid ratio "%s"', $ratio));
        }

        if (! $sourceDir) {
            $sourceDir = __DIR__ . "/../../resources/faker/images/{$ratio}/";
        }

        return fake()->file($sourceDir);
    }

    public function tags($max = 3, $allowed = []): array
    {
        if (! count($allowed)) {
            $allowed = [
                'Economia',
                'Financiamento',
                'Projetos',
                'Sustentabilidade',
                'Tendências',
            ];
        }

        return fake()->randomElements($allowed, fake()->numberBetween(1, $max));
    }

    public function htmlParagraph($nbSentences = 3): string
    {
        return '<p>' . fake()->paragraph($nbSentences) . '</p>';
    }

    public function htmlParagraphs($nbParagraphs = 10): string
    {
        return collect(range(1, $nbParagraphs))
            ->map(fn () => fake()->paragraph(10))
            ->map(fn ($paragraph) => $this->saltTags($paragraph))
            ->map(fn ($paragraph) => "<p>{$paragraph}</p>")
            ->implode('');
    }

    public function htmlText(): string
    {
        $blocks[] = $this->htmlParagraphs(fake()->numberBetween(3, 6));
        $blocks[] = sprintf("<p><img src=\"%s\" width=\"800\" style=\"width\: 800; height\: 600;\" height=\"600\" data-id=\"%s\"></p>", "https://picsum.photos/800/600?random=" . random_int(10000, 15999), fake()->uuid());
        $blocks[] = sprintf('<blockquote>%s</blockquote>', $this->htmlParagraphs(fake()->numberBetween(1, 3)));
        $blocks[] = sprintf('<details><summary>%s</summary><div data-type="detailsContent">%s</div></details>', fake()->sentence(3), $this->htmlParagraphs(fake()->numberBetween(1, 3)));

        return implode('', $blocks);
    }

    private function saltTags($text, $nbTags = 3): string
    {
        $tags = [
            '<strong>%s</strong>',
            '<em>%s</em>',
            '<u>%s</u>',
            '<s>%s</s>',
            '<a href="https://fmd.ag">%s</a>',
        ];
        shuffle($tags);
        $words = explode(' ', $text);
        shuffle($words);
        $taggedWords = [];
        for ($i = 0; $i < $nbTags; $i++) {
            $taggedWords[$words[$i]] = sprintf($tags[$i], $words[$i]);
        }

        return str_replace(array_keys($taggedWords), array_values($taggedWords), $text);
    }
}
