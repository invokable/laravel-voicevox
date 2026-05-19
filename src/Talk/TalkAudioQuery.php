<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Talk;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\VoicevoxResponse;

class TalkAudioQuery
{
    use Conditionable;
    use Macroable;
    use Tappable;

    public function __construct(
        public array $audioQuery,
        public int|string|null $id = null,
    ) {
        //
    }

    public function generate(int|string $id = 1, bool $enableInterrogativeUpspeak = true): VoicevoxResponse
    {
        $audio = Synthesizer::synthesis(
            json_encode($this->audioQuery),
            (int) $id,
            $enableInterrogativeUpspeak,
        );

        return new VoicevoxResponse($audio);
    }
}
