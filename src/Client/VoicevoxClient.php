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

    /**
     * Engine Manifest.
     */
    public function manifest(): array
    {
        return $this->http()->get('/engine_manifest')->json();
    }

    /**
     * Get Presets.
     */
    public function presets(): array
    {
        return $this->http()->get('/presets')->json();
    }

    /**
     * Add Preset.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function addPreset(array $preset): int
    {
        return $this->http()->post('/add_preset', $preset)->throw()->json();
    }

    /**
     * Update Preset.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updatePreset(array $preset): int
    {
        return $this->http()->post('/update_preset', $preset)->throw()->json();
    }

    /**
     * Delete Preset.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function deletePreset(int $id): void
    {
        $this->http()->withQueryParameters(['id' => $id])->post('/delete_preset')->throw();
    }

    /**
     * Get User Dictionary Words.
     */
    public function userDict(): array
    {
        return $this->http()->get('/user_dict')->json();
    }

    /**
     * Add a word to User Dictionary.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function addWord(string $surface, string $pronunciation, int $accent_type, ?string $word_type = null, ?int $priority = null): string
    {
        return $this->http()->withQueryParameters(array_filter([
            'surface' => $surface,
            'pronunciation' => $pronunciation,
            'accent_type' => $accent_type,
            'word_type' => $word_type,
            'priority' => $priority,
        ], fn ($v) => ! is_null($v)))->post('/user_dict_word')->throw()->json();
    }

    /**
     * Update a word in User Dictionary.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateWord(string $word_uuid, string $surface, string $pronunciation, int $accent_type, ?string $word_type = null, ?int $priority = null): void
    {
        $this->http()->withQueryParameters(array_filter([
            'surface' => $surface,
            'pronunciation' => $pronunciation,
            'accent_type' => $accent_type,
            'word_type' => $word_type,
            'priority' => $priority,
        ], fn ($v) => ! is_null($v)))->put("/user_dict_word/{$word_uuid}")->throw();
    }

    /**
     * Delete a word from User Dictionary.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function deleteWord(string $word_uuid): void
    {
        $this->http()->delete("/user_dict_word/{$word_uuid}")->throw();
    }
}
