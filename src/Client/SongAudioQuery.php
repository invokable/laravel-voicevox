<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Traits\Tappable;

class SongAudioQuery
{
    use Tappable;
    use WithHttp;

    public function __construct(public array $score, public array $frame_audio_query)
    {
        //
    }

    /**
     * Synthesize singing audio from a FrameAudioQuery.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function generate(array $frame_audio_query, int|string $id, ?string $core_version = null): SongResponse
    {
        $body = $this->http()->withQueryParameters(array_filter([
            'speaker' => $id,
            'core_version' => $core_version,
        ], fn ($v) => ! is_null($v)))
            ->post('frame_synthesis', $frame_audio_query)
            ->throw()
            ->body();

        return new SongResponse($body);
    }
}
