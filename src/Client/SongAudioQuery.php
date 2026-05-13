<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Traits\Tappable;
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\VoicevoxResponse;

class SongAudioQuery
{
    use Tappable;
    use WithHttp;

    public function __construct(
        public array $score,
        public array $frame_audio_query,
        public int|string $id,
    ) {
        //
    }

    /**
     * Generate song.
     *
     * @param  int|string  $id  typeがframe_decodeかsingのスタイルID
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function generate(int|string $id, ?string $core_version = null): VoicevoxResponse
    {
        $body = Voicevox::frameSynthesis($this->frame_audio_query, $id, $core_version);

        return new VoicevoxResponse($body);
    }
}
