<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Song;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\VoicevoxResponse;

class SongAudioQuery
{
    use Conditionable;
    use Macroable;
    use Tappable;

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
     * @param  int|string  $id  typeがframe_decodeかsingのスタイルID
     */
    public function generate(int|string $id): VoicevoxResponse
    {
        $audio = Synthesizer::frameSynthesis(
            json_encode($this->frameAudioQuery),
            (int) $id,
        );

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
        $this->frameAudioQuery['f0'] = json_decode(Synthesizer::createSingFrameF0(json_encode($this->score), json_encode($this->frameAudioQuery), (int) $this->teacher), true);

        return $this;
    }

    public function updateVolume(): self
    {
        $this->frameAudioQuery['volume'] = json_decode(Synthesizer::createSingFrameVolume(json_encode($this->score), json_encode($this->frameAudioQuery), (int) $this->teacher), true);

        return $this;
    }
}
