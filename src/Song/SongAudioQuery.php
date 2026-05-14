<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Song;

use Illuminate\Support\Traits\Tappable;
use Revolution\Voicevox\Core\Synthesizer;
use Revolution\Voicevox\VoicevoxResponse;

class SongAudioQuery
{
    use Tappable;

    public function __construct(
        public array $frameAudioQuery,
        public int|string|null $teacher = null,
    ) {
        //
    }

    /**
     * @param  int|string  $id  typeがframe_decodeかsingのスタイルID
     */
    public function generate(int|string $id): VoicevoxResponse
    {
        $wav = app(Synthesizer::class)->frameSynthesis(
            collect($this->frameAudioQuery)->toJson(JSON_UNESCAPED_SLASHES),
            (int) $id,
        );

        return new VoicevoxResponse($wav);
    }
}
