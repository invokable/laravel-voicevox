<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

trait HasSong
{
    /**
     * Create a FrameAudioQuery for singing synthesis from a Score.
     *
     * @param  int|string  $teacher  typeがsingかsinging_teacherのスタイルID
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function singFrameAudioQuery(array|Arrayable $score, int|string $teacher = 6000): array
    {
        $score = $score instanceof Arrayable ? $score->toArray() : $score;

        $response = $this->http()->withQueryParameters(array_filter([
            'speaker' => $teacher,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('sing_frame_audio_query', $score)
            ->throw();

        return $response->json();
    }

    /**
     * Get frame-by-frame fundamental frequency (F0) from a Score and FrameAudioQuery.
     *
     * @param  int|string  $teacher  typeがsingかsinging_teacherのスタイルID
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function singFrameF0(array|Arrayable $score, array $frameAudioQuery, int|string $teacher = 6000): array
    {
        $score = $score instanceof Arrayable ? $score->toArray() : $score;

        return $this->http()->withQueryParameters(array_filter([
            'speaker' => $teacher,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('sing_frame_f0', ['score' => $score, 'frame_audio_query' => $frameAudioQuery])
            ->throw()
            ->json();
    }

    /**
     * Get frame-by-frame volume from a Score and FrameAudioQuery.
     *
     * @param  int|string  $teacher  typeがsingかsinging_teacherのスタイルID
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function singFrameVolume(array|Arrayable $score, array $frameAudioQuery, int|string $teacher = 6000): array
    {
        $score = $score instanceof Arrayable ? $score->toArray() : $score;

        return $this->http()->withQueryParameters(array_filter([
            'speaker' => $teacher,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('sing_frame_volume', ['score' => $score, 'frame_audio_query' => $frameAudioQuery])
            ->throw()
            ->json();
    }

    /**
     * Synthesize singing audio from a FrameAudioQuery.
     *
     * @param  int|string  $id  typeがframe_decodeかsingのスタイルID
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function frameSynthesis(array $frameAudioQuery, int|string $id): string
    {
        $response = $this->http()
            ->accept('audio/wav')
            ->withQueryParameters(array_filter([
                'speaker' => $id,
                'core_version' => config('voicevox.client.core_version'),
            ], fn ($v) => ! is_null($v)))
            ->post('frame_synthesis', $frameAudioQuery)
            ->throw();

        return $response->body();
    }

    /**
     * Get Singers (singing voice speakers).
     */
    public function singers(): array
    {
        return $this->http()->get('singers', array_filter([
            'core_version' => config('voicevox.client.core_version'),
        ]))->json();
    }

    /**
     * Singer Info.
     */
    public function singer(string $uuid, string $format = 'base64'): array
    {
        return $this->http()->get('singer_info', array_filter([
            'speaker_uuid' => $uuid,
            'resource_format' => $format,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))->json();
    }
}
