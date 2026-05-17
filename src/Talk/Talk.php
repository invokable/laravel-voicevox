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

    /**
     * テキスト音声合成。
     */
    public function talk(string $text, int|string $id = 1): TalkAudioQuery
    {
        $audioQuery = json_decode(Synthesizer::createAudioQuery($text, $id), true);

        return new TalkAudioQuery($audioQuery, $id);
    }

    /**
     * AquesTalk風記法カタカナから音声合成。
     */
    public function kana(string $kana, int|string $id = 1): TalkAudioQuery
    {
        $audioQuery = json_decode(Synthesizer::createAudioQueryFromKana($kana, $id), true);

        return new TalkAudioQuery($audioQuery, $id);
    }
}
