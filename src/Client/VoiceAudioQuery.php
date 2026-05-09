<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Traits\Tappable;

class VoiceAudioQuery
{
    use Tappable;
    use WithHttp;

    public function __construct(public array $audio_query)
    {
        //
    }

    /**
     * Generate voice.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function generate(int|string $id = 1, bool $upspeak = true, ?int $core_version = null): VoiceResponse
    {
        $response = $this->http()
            ->accept('audio/wav')
            ->withBody(collect($this->audio_query)->toPrettyJson(JSON_UNESCAPED_SLASHES))
            ->withQueryParameters(array_filter([
                'speaker' => $id,
                'enable_interrogative_upspeak' => $upspeak,
                'core_version' => $core_version,
            ], fn ($v) => ! is_null($v)))
            ->post('synthesis')
            ->throw();

        return new VoiceResponse($response->body());
    }
}
