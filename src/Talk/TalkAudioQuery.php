<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Talk;

use Illuminate\Support\Traits\Tappable;
use Revolution\Voicevox\Core\Synthesizer;

class TalkAudioQuery
{
    use Tappable;

    public function __construct(
        protected Synthesizer $synthesizer,
        public array $audio_query,
        public int|string|null $id = null,
    ) {
        //
    }

    public function generate(int|string $id = 1): TalkResponse
    {
        $wav = $this->synthesizer->synthesis(collect($this->audio_query)->toPrettyJson(JSON_UNESCAPED_SLASHES), $id);

        return new TalkResponse($wav);
    }
}
