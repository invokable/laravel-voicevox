<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Ai;

use Laravel\Ai\Contracts\Gateway\AudioGateway;
use Laravel\Ai\Contracts\Providers\AudioProvider;
use Laravel\Ai\Responses\AudioResponse;
use Laravel\Ai\Responses\Data\Meta;
use Revolution\Voicevox\Ai\Concerns\ResolvesVoiceId;
use Revolution\Voicevox\Talk\Talk;

class VoicevoxGateway implements AudioGateway
{
    use ResolvesVoiceId;

    /**
     * Generate audio from the given text using the native VOICEVOX core.
     *
     * The $voice parameter accepts a numeric string (VOICEVOX style ID)
     * or the convenience aliases defined in {@see ResolvesVoiceId}.
     */
    public function generateAudio(
        AudioProvider $provider,
        string $model,
        string $text,
        string $voice,
        ?string $instructions = null,
        int $timeout = 30,
    ): AudioResponse {
        $id = $this->resolveVoiceId($voice);

        $response = Talk::make()->talk($text, $id)->generate($id);

        return new AudioResponse(
            $response->toBase64(),
            new Meta($provider->name(), $voice),
            'audio/wav',
        );
    }
}
