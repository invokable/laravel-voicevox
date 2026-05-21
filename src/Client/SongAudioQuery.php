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

class SongAudioQuery
{
    use Conditionable;
    use Macroable;
    use Tappable;
    use WithHttp;

    /**
     * @param  array{notes: array}  $score
     * @param  array{
     *     f0: array<int, float>,
     *     volume: array<int, float>,
     *     phonemes: array,
     *     volumeScale: float,
     *     outputSamplingRate: int,
     *     outputStereo: bool,
     * }  $frameAudioQuery
     */
    public function __construct(
        public array $score,
        public array $frameAudioQuery,
        public int|string $teacher,
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
    public function generate(int|string $id): VoicevoxResponse
    {
        $audio = Voicevox::frameSynthesis($this->frameAudioQuery, $id);

        return new VoicevoxResponse($audio);
    }

    /**
     * Score変更後に、f0とvolumeを更新する。
     */
    public function sync(): self
    {
        return $this->updateF0()->updateVolume();
    }

    public function updateF0(): self
    {
        $this->frameAudioQuery['f0'] = Voicevox::singFrameF0($this->score, $this->frameAudioQuery, (int) $this->teacher);

        return $this;
    }

    public function updateVolume(): self
    {
        $this->frameAudioQuery['volume'] = Voicevox::singFrameVolume($this->score, $this->frameAudioQuery, (int) $this->teacher);

        return $this;
    }
}
