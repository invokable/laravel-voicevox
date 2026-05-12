<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Talk;

use Revolution\Voicevox\Core\Synthesizer;

class Talk
{
    protected Synthesizer $synthesizer;

    public function __construct()
    {
        $this->synthesizer = app(Synthesizer::class);
    }

    public static function make(string $text, int|string $id = 1): TalkAudioQuery
    {
        return (new self)->talk($text, $id);
    }

    public function talk(string $text, int|string $id = 1): TalkAudioQuery
    {
        $audio_query = json_decode($this->synthesizer->createAudioQuery($text, $id), true);

        return new TalkAudioQuery($this->synthesizer, $audio_query, $id);
    }
}
