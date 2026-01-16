<?php

declare(strict_types=1);

namespace Agenciafmd\Support\Faker;

use Faker\Provider\Base;

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
        switch (self::numberBetween(1, 3)) {
            case 1:
                return $this->youtubeUri();

                break;

            case 2:
                return $this->youtubeShortUri();

                break;

            case 3:
                return $this->youtubeEmbedUri();

                break;

            default:
                return $this->youtubeUri();

                break;
        }
    }
}
