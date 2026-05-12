<?php

declare(strict_types=1);

namespace Revolution\Voicevox;

use Revolution\Voicevox\Talk\Talk;
use Revolution\Voicevox\Talk\TalkAudioQuery;

function talk(string $text, int|string $id = 1): TalkAudioQuery
{
    return Talk::make($text, $id);
}
