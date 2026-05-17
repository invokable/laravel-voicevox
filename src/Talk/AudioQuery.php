<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Talk;

use Illuminate\Contracts\Support\Arrayable;

class AudioQuery implements Arrayable
{
    public function __construct(
        public array $accent_phrases = [],
        public float $speedScale = 1.0,
        public float $pitchScale = 0.0,
        public float $intonationScale = 1.0,
        public float $volumeScale = 1.0,
        public float $prePhonemeLength = 0.1,
        public float $postPhonemeLength = 0.1,
        public int $outputSamplingRate = 24000,
        public bool $outputStereo = false,
        public string $kana = '',
    ) {
        //
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
