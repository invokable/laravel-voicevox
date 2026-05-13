<?php

declare(strict_types=1);

namespace Revolution\Voicevox;

use Revolution\Voicevox\Song\Song;
use Revolution\Voicevox\Song\SongAudioQuery;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\Talk\Talk;
use Revolution\Voicevox\Talk\TalkAudioQuery;

function talk(string $text, int|string $id = 1): TalkAudioQuery
{
    return Talk::make($text, $id);
}

function song(Score|array $score, int|string $id = 1): SongAudioQuery
{
    return Song::make($score, $id);
}
