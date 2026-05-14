<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Song;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Voicevox\Core\Synthesizer;

class Song
{
    /**
     * @param  int|string  $teacher  typeがsingかsinging_teacherのスタイルID
     */
    public static function make(Score|array $score, int|string $teacher = 6000): SongAudioQuery
    {
        return (new self)->song($score, $teacher);
    }

    /**
     * @param  int|string  $teacher  typeがsingかsinging_teacherのスタイルID
     */
    public function song(Score|array $score, int|string $teacher = 6000): SongAudioQuery
    {
        $score = $score instanceof Arrayable ? $score->toArray() : $score;

        $frameAudioQuery = json_decode(
            app(Synthesizer::class)->createSingFrameAudioQuery(
                collect($score)->toJson(JSON_UNESCAPED_SLASHES),
                (int) $teacher,
            ),
            true,
        );

        return new SongAudioQuery($frameAudioQuery, $teacher);
    }
}
