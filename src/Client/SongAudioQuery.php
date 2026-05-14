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
        public array $frameAudioQuery,
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
    public function generate(int|string $id, ?string $coreVersion = null): VoicevoxResponse
    {
        $body = Voicevox::frameSynthesis($this->frameAudioQuery, $id, $coreVersion);

        return new VoicevoxResponse($body);
    }
}
