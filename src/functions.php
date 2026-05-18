<?php

declare(strict_types=1);

namespace Revolution\Voicevox;

use Revolution\Voicevox\Engine\NativePresetStore;
use Revolution\Voicevox\Engine\NativeUserDict;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\Song\Song;
use Revolution\Voicevox\Song\SongAudioQuery;
use Revolution\Voicevox\Talk\Talk;
use Revolution\Voicevox\Talk\TalkAudioQuery;

/**
 * テキスト音声合成。
 */
function talk(string $text, int|string $id = 1): TalkAudioQuery
{
    return Talk::make()->talk($text, $id);
}

/**
 * AquesTalk風記法カタカナから音声合成。
 */
function kana(string $kana, int|string $id = 1): TalkAudioQuery
{
    return Talk::make()->kana($kana, $id);
}

/**
 * 歌声合成。
 *
 * @param  int|string  $teacher  typeがsingかsinging_teacherのスタイルID
 */
function song(Score|array $score, int|string $teacher = 6000): SongAudioQuery
{
    return Song::make()->song($score, $teacher);
}

function dict(): NativeUserDict
{
    return app(NativeUserDict::class);
}

function preset(): NativePresetStore
{
    return app(NativePresetStore::class);
}
