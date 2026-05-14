<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Ai;

use Illuminate\Support\Facades\Http;
use Laravel\Ai\Contracts\Gateway\AudioGateway;
use Laravel\Ai\Contracts\Providers\AudioProvider;
use Laravel\Ai\Responses\AudioResponse;
use Laravel\Ai\Responses\Data\Meta;

class VoicevoxGateway implements AudioGateway
{
    /**
     * Generate audio from the given text using the VOICEVOX engine.
     *
     * The $voice parameter accepts a numeric string (VOICEVOX style ID)
     * or the convenience aliases 'default-female' (→ 1) and 'default-male' (→ 3).
     */
    public function generateAudio(
        AudioProvider $provider,
        string $model,
        string $text,
        string $voice,
        ?string $instructions = null,
        int $timeout = 30,
    ): AudioResponse {
        $speakerId = match ($voice) {
            'default-female' => 1,
            'default-male' => 3,
            default => (int) $voice,
        };

        $config = $provider->providerCredentials();
        $baseUrl = $config['key'] ?? 'http://127.0.0.1:50021';

        $client = Http::baseUrl($baseUrl)->timeout($timeout);

        $audioQuery = $client
            ->withQueryParameters(['text' => $text, 'speaker' => $speakerId])
            ->post('audio_query')
            ->throw()
            ->json();

        $wav = $client
            ->accept('audio/wav')
            ->withQueryParameters(['speaker' => $speakerId])
            ->post('synthesis', $audioQuery)
            ->throw()
            ->body();

        return new AudioResponse(
            base64_encode($wav),
            new Meta($provider->name(), $model),
            'audio/wav',
        );
    }
}
