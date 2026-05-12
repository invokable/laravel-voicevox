<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Talk;

use Revolution\Voicevox\Core\Synthesizer;

class Talk
{
    public static function make(string $text, int|string $id = 1): TalkAudioQuery
    {
        return (new self)->talk($text, $id);
    }

    public function talk(string $text, int|string $id = 1): TalkAudioQuery
    {
        $audio_query = json_decode(app(Synthesizer::class)->createAudioQuery($text, $id), true);

        return new TalkAudioQuery($audio_query, $id);
    }
}
