<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Talk;

use Illuminate\Support\Traits\Tappable;
use Revolution\Voicevox\Core\Synthesizer;
use Revolution\Voicevox\VoicevoxResponse;

class TalkAudioQuery
{
    use Tappable;

    public function __construct(
        public array $audio_query,
        public int|string|null $id = null,
    ) {
        //
    }

    public function generate(int|string $id = 1): VoicevoxResponse
    {
        $wav = app(Synthesizer::class)->synthesis(
            collect($this->audio_query)->toPrettyJson(JSON_UNESCAPED_SLASHES),
            (int) $id,
        );

        return new VoicevoxResponse($wav);
    }
}
