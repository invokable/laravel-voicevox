<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class VoicevoxClient
{
    use WithHttp;

    /**
     * Create Audio Query.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function voice(string $text, int $speaker = 1, bool $katakana_english = true, ?int $core_version = null): VoiceAudioQuery
    {
        $response = $this->http()->withQueryParameters([
            'text' => $text,
            'speaker' => $speaker,
            'enable_katakana_english' => $katakana_english,
            'core_version' => $core_version,
        ])->post('/audio_query')
            ->throw();

        return new VoiceAudioQuery($response->json());
    }

    /**
     * Get Speakers.
     */
    public function speakers(?int $core_version = null): array
    {
        return $this->http()->get('/speakers', [
            'core_version' => $core_version,
        ])->json();
    }

    /**
     * Speaker Info.
     */
    public function speaker(string $uuid, string $format = 'base64', ?int $core_version = null): array
    {
        return $this->http()->get('/speaker_info', [
            'speaker_uuid' => $uuid,
            'resource_format' => $format,
            'core_version' => $core_version,
        ])->json();
    }

    /**
     * Engine Version.
     */
    public function version(): string
    {
        return $this->http()->get('/version')->body();
    }
}
