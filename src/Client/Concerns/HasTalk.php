<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client\Concerns;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

trait HasTalk
{
    /**
     * Create Audio Query.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function audioQuery(string $text, int|string $id = 1, bool $enableKatakanaEnglish = true): array
    {
        $response = $this->http()->withQueryParameters(array_filter([
            'text' => $text,
            'speaker' => $id,
            'enable_katakana_english' => $enableKatakanaEnglish,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('audio_query')
            ->throw();

        return $response->json();
    }

    /**
     * Get accent phrases from text.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function accentPhrases(string $text, int|string $id, bool $isKana = false, bool $katakanaEnglish = true): array
    {
        return $this->http()->withQueryParameters(array_filter([
            'text' => $text,
            'speaker' => $id,
            'is_kana' => $isKana,
            'enable_katakana_english' => $katakanaEnglish,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('accent_phrases')
            ->throw()
            ->json();
    }

    /**
     * Synthesize talk audio from an AudioQuery.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function synthesis(array $audioQuery, int|string $id = 1, bool $enableInterrogativeUpspeak = true): string
    {
        $response = $this->http()
            ->accept('audio/wav')
            ->withQueryParameters(array_filter([
                'speaker' => $id,
                'enable_interrogative_upspeak' => $enableInterrogativeUpspeak,
                'core_version' => config('voicevox.client.core_version'),
            ], fn ($v) => ! is_null($v)))
            ->post('synthesis', $audioQuery)
            ->throw();

        return $response->body();
    }

    /**
     * Get mora phoneme length and pitch from accent phrases.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function moraData(array $accentPhrases, int|string $id): array
    {
        return $this->http()->withQueryParameters(array_filter([
            'speaker' => $id,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('mora_data', $accentPhrases)
            ->throw()
            ->json();
    }

    /**
     * Get mora phoneme lengths from accent phrases.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function moraLength(array $accentPhrases, int|string $id): array
    {
        return $this->http()->withQueryParameters(array_filter([
            'speaker' => $id,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('mora_length', $accentPhrases)
            ->throw()
            ->json();
    }

    /**
     * Get mora pitches from accent phrases.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function moraPitch(array $accentPhrases, int|string $id): array
    {
        return $this->http()->withQueryParameters(array_filter([
            'speaker' => $id,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('mora_pitch', $accentPhrases)
            ->throw()
            ->json();
    }

    /**
     * Get Speakers.
     */
    public function speakers(): array
    {
        return $this->http()->get('speakers', array_filter([
            'core_version' => config('voicevox.client.core_version'),
        ]))->json();
    }

    /**
     * Speaker Info.
     */
    public function speaker(string $uuid, string $format = 'base64'): array
    {
        return $this->http()->get('speaker_info', array_filter([
            'speaker_uuid' => $uuid,
            'resource_format' => $format,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))->json();
    }
}
