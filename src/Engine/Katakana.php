<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine;

use Revolution\Voicevox\Synthesizer;

class Katakana
{
    /**
     * テキストからAquesTalk風記法カタカナに変換。
     * コアを使ってAudioQueryを作ってからkanaを抽出する方法。
     */
    public function create(string $text, int|string $id = 1): string
    {
        $audio_query = json_decode(Synthesizer::createAudioQuery($text, $id), true);

        return $audio_query['kana'] ?? '';
    }
}
