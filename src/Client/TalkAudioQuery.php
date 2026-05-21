<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use Revolution\Voicevox\Client\Concerns\WithHttp;
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\VoicevoxResponse;

class TalkAudioQuery
{
    use Conditionable;
    use Macroable;
    use Tappable;
    use WithHttp;

    /**
     * @param  array{
     *     accent_phrases: array,
     *     speedScale: float,
     *     pitchScale: float,
     *     intonationScale: float,
     *     volumeScale: float,
     *     prePhonemeLength: float,
     *     postPhonemeLength: float,
     *     outputSamplingRate: int,
     *     outputStereo: bool,
     *     kana: string,
     * }  $audioQuery
     */
    public function __construct(
        public array $audioQuery,
        public int|string|null $id = null,
    ) {
        //
    }

    /**
     * Generate talk.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function generate(int|string $id = 1, bool $enableInterrogativeUpspeak = true): VoicevoxResponse
    {
        $audio = Voicevox::synthesis($this->audioQuery, $id, $enableInterrogativeUpspeak);

        return new VoicevoxResponse($audio);
    }
}
