<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Ai\Concerns;

trait ResolvesVoiceId
{
    /**
     * Resolve a voice alias or numeric string to a VOICEVOX style ID.
     *
     * Named aliases are provided for convenience; any other value is cast to int
     * and used as a raw style ID.
     */
    protected function resolveVoiceId(string $voice): int
    {
        return match ($voice) {
            'ずんだもん' => 1,
            '四国めたん' => 2,
            '春日部つむぎ' => 8,
            'default-female' => 10,
            'default-male' => 12,
            default => (int) $voice,
        };
    }
}
