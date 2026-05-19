<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Song;

use Illuminate\Support\Traits\Tappable;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\VoicevoxResponse;

class SongAudioQuery
{
    use Tappable;

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
        $this->frameAudioQuery['f0'] = Synthesizer::createSingFrameF0(json_encode($this->score), json_encode($this->frameAudioQuery), (int) $this->teacher);

        return $this;
    }

    public function updateVolume(): self
    {
        $this->frameAudioQuery['volume'] = Synthesizer::createSingFrameVolume(json_encode($this->score), json_encode($this->frameAudioQuery), (int) $this->teacher);

        return $this;
    }
}
