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

    public function kana(string $kana, int|string $id = 1): TalkAudioQuery
    {
        $accent_phrases = json_decode(Synthesizer::createAccentPhrasesFromKana($kana, $id), true);

        $audioQuery = [
            'accent_phrases' => $accent_phrases,
            'speedScale' => 1.0,
            'pitchScale' => 0.0,
            'intonationScale' => 1.0,
            'volumeScale' => 1.0,
            'prePhonemeLength' => 0.1,
            'postPhonemeLength' => 0.1,
            'outputSamplingRate' => 24000,
            'outputStereo' => false,
            'kana' => $kana,
        ];

        return new TalkAudioQuery($audioQuery, $id);
    }
}
