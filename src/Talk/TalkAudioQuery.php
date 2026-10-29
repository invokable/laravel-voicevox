<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Talk;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;
use InvalidArgumentException;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\VoicevoxResponse;

class TalkAudioQuery
{
    use Conditionable;
    use Macroable;
    use Tappable;

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

    public function generate(int|string $id = 1, bool $enableInterrogativeUpspeak = true): VoicevoxResponse
    {
        $audio = Synthesizer::synthesis(
            json_encode($this->audioQuery),
            (int) $id,
            $enableInterrogativeUpspeak,
        );

        return new VoicevoxResponse($audio);
    }

    /**
     * ストリーミング音声合成。公式エンジンの`/streaming_synthesis`と同じ仕様。
     *
     * `streaming_talk`スタイルのみ対応。
     *
     * @param  float  $startOffset  音声の開始位置（秒）
     * @param  float  $segmentLength  一度に合成する音声の長さ（秒）
     *
     * @throws InvalidArgumentException `$startOffset`が音声の長さ以上の場合
     */
    public function stream(
        int|string $id = 1,
        float $startOffset = 0.0,
        float $segmentLength = 0.3,
        bool $enableInterrogativeUpspeak = true,
    ): TalkStream {
        $audioFeature = Synthesizer::createAudioFeature(
            json_encode($this->audioQuery),
            (int) $id,
            $enableInterrogativeUpspeak,
        );

        return new TalkStream(
            totalFrames: $audioFeature->length(),
            samplingRate: (int) ($this->audioQuery['outputSamplingRate'] ?? 24000),
            stereo: (bool) ($this->audioQuery['outputStereo'] ?? false),
            renderer: fn (int $start, int $end): string => $audioFeature->slice($start, $end)->render(),
            startOffset: $startOffset,
            segmentLength: $segmentLength,
        );
    }
}
