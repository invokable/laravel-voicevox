<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Song;

use Illuminate\Support\Traits\Tappable;
use Revolution\Voicevox\Core\Synthesizer;

class SongAudioQuery
{
    use Tappable;

    public function __construct(
        public array $frame_audio_query,
        public int|string|null $id = null,
    ) {
        //
    }

    /**
     * @param  int|string  $id  typeがframe_decodeかsingのスタイルID
     */
    public function generate(int|string $id): SongResponse
    {
        $wav = app(Synthesizer::class)->frameSynthesis(
            collect($this->frame_audio_query)->toJson(JSON_UNESCAPED_SLASHES),
            (int) $id,
        );

        return new SongResponse($wav);
    }
}
