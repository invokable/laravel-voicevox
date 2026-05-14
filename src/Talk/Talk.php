<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Talk;

use Revolution\Voicevox\Synthesizer;

class Talk
{
    public static function make(): self
    {
        return new self;
    }

    public function talk(string $text, int|string $id = 1): TalkAudioQuery
    {
        $audioQuery = json_decode(Synthesizer::createAudioQuery($text, $id), true);

        return new TalkAudioQuery($audioQuery, $id);
    }
}
