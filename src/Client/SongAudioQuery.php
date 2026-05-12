<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Traits\Tappable;
use Revolution\Voicevox\Voicevox;

class SongAudioQuery
{
    use Tappable;
    use WithHttp;

    public function __construct(
        public array $frame_audio_query,
        public array $score,
        public int|string $id,
    ) {
        //
    }

    /**
     * Generate song.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function generate(int|string|null $id = null, ?string $core_version = null): SongResponse
    {
        $body = Voicevox::frameSynthesis($this->frame_audio_query, $id ?? $this->id, $core_version);

        return new SongResponse($body);
    }
}
