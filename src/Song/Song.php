<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Song;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Voicevox\Synthesizer;

class Song
{
    public static function make(): self
    {
        return new self;
    }

    /**
     * @param  int|string  $teacher  typeがsingかsinging_teacherのスタイルID
     */
    public function song(Score|array $score, int|string $teacher = 6000): SongAudioQuery
    {
        $score = $score instanceof Arrayable ? $score->toArray() : $score;

        $frameAudioQuery = json_decode(
            Synthesizer::createSingFrameAudioQuery(
                collect($score)->toJson(JSON_UNESCAPED_SLASHES),
                (int) $teacher,
            ),
            true,
        );

        return new SongAudioQuery($frameAudioQuery, $teacher);
    }
}
