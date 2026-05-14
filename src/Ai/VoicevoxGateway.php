<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Ai;

use Laravel\Ai\Contracts\Gateway\AudioGateway;
use Laravel\Ai\Contracts\Providers\AudioProvider;
use Laravel\Ai\Responses\AudioResponse;
use Laravel\Ai\Responses\Data\Meta;
use Revolution\Voicevox\Talk\Talk;

class VoicevoxGateway implements AudioGateway
{
    /**
     * Generate audio from the given text using the VOICEVOX engine.
     *
     * The $voice parameter accepts a numeric string (VOICEVOX style ID)
     * or the convenience aliases
     * 'ずんだもん' (→ 1)
     * '四国めたん' (→ 2)
     * '春日部つむぎ'(→ 8)
     * 'default-male' 白上虎太郎 (→ 12)
     * 'default-female' 雨晴はう (→ 10)
     */
    public function generateAudio(
        AudioProvider $provider,
        string $model,
        string $text,
        string $voice,
        ?string $instructions = null,
        int $timeout = 30,
    ): AudioResponse {
        $id = match ($voice) {
            'ずんだもん' => 1,
            '四国めたん' => 2,
            '春日部つむぎ' => 8,
            'default-male' => 12,
            'default-female' => 10,
            default => (int) $voice,
        };

        $response = Talk::make()->talk($text, $id)->generate($id);

        return new AudioResponse(
            $response->toBase64(),
            new Meta($provider->name(), $voice),
            'audio/wav',
        );
    }
}
