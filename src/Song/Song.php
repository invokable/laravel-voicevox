<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Song;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Voicevox\Core\Synthesizer;

class Song
{
    public static function make(Score|array $score, int|string $id = 1): SongAudioQuery
    {
        return (new self)->song($score, $id);
    }

    public function song(Score|array $score, int|string $id = 1): SongAudioQuery
    {
        $score = $score instanceof Arrayable ? $score->toArray() : $score;

        $frame_audio_query = json_decode(
            app(Synthesizer::class)->createSingFrameAudioQuery(
                collect($score)->toJson(JSON_UNESCAPED_SLASHES),
                (int) $id,
            ),
            true,
        );

        return new SongAudioQuery($frame_audio_query, $id);
    }
}
