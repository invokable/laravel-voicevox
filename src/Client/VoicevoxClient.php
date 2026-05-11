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
    public function talk(string $text, int|string $id = 1, bool $katakana_english = true, ?int $core_version = null): TalkAudioQuery
    {
        $response = $this->http()->withQueryParameters(array_filter([
            'text' => $text,
            'speaker' => $id,
            'enable_katakana_english' => $katakana_english,
            'core_version' => $core_version,
        ], fn ($v) => ! is_null($v)))
            ->post('audio_query')
            ->throw();

        return new TalkAudioQuery($response->json());
    }

    /**
     * Get Speakers.
     */
    public function speakers(?int $core_version = null): array
    {
        return $this->http()->get('speakers', array_filter([
            'core_version' => $core_version,
        ]))->json();
    }

    /**
     * Speaker Info.
     */
    public function speaker(string $uuid, string $format = 'base64', ?int $core_version = null): array
    {
        return $this->http()->get('speaker_info', array_filter([
            'speaker_uuid' => $uuid,
            'resource_format' => $format,
            'core_version' => $core_version,
        ], fn ($v) => ! is_null($v)))->json();
    }

    /**
     * Engine Version.
     */
    public function version(): string
    {
        return $this->http()->get('version')->body();
    }

    /**
     * Engine Manifest.
     */
    public function manifest(): array
    {
        return $this->http()->get('engine_manifest')->json();
    }

    /**
     * Get Presets.
     */
    public function presets(): array
    {
        return $this->http()->get('presets')->json();
    }

    /**
     * Add Preset.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function addPreset(array $preset): int
    {
        return $this->http()->post('add_preset', $preset)->throw()->json();
    }

    /**
     * Update Preset.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updatePreset(array $preset): int
    {
        return $this->http()->post('update_preset', $preset)->throw()->json();
    }

    /**
     * Delete Preset.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function deletePreset(int $id): void
    {
        $this->http()->withQueryParameters(['id' => $id])->post('delete_preset')->throw();
    }

    /**
     * Get User Dictionary Words.
     */
    public function userDict(): array
    {
        return $this->http()->get('user_dict')->json();
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
        ], fn ($v) => ! is_null($v)))
            ->post('user_dict_word')
            ->throw()
            ->json();
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
        ], fn ($v) => ! is_null($v)))
            ->put("user_dict_word/{$word_uuid}")
            ->throw();
    }

    /**
     * Delete a word from User Dictionary.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function deleteWord(string $word_uuid): void
    {
        $this->http()->delete("user_dict_word/{$word_uuid}")->throw();
    }

    /**
     * Import User Dictionary.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function importUserDict(array $words, bool $override = false): void
    {
        $this->http()->withQueryParameters(['override' => $override])
            ->post('import_user_dict', $words)->throw();
    }

    /**
     * Create Audio Query from Preset.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function talkFromPreset(string $text, int $preset_id, bool $katakana_english = true, ?int $core_version = null): TalkAudioQuery
    {
        $response = $this->http()->withQueryParameters(array_filter([
            'text' => $text,
            'preset_id' => $preset_id,
            'enable_katakana_english' => $katakana_english,
            'core_version' => $core_version,
        ], fn ($v) => ! is_null($v)))
            ->post('audio_query_from_preset')
            ->throw();

        return new TalkAudioQuery($response->json());
    }

    /**
     * Get Singers (singing voice speakers).
     */
    public function singers(?int $core_version = null): array
    {
        return $this->http()->get('singers', array_filter([
            'core_version' => $core_version,
        ]))->json();
    }

    /**
     * Singer Info.
     */
    public function singer(string $uuid, string $format = 'base64', ?int $core_version = null): array
    {
        return $this->http()->get('singer_info', array_filter([
            'speaker_uuid' => $uuid,
            'resource_format' => $format,
            'core_version' => $core_version,
        ], fn ($v) => ! is_null($v)))->json();
    }

    /**
     * List available core versions.
     */
    public function coreVersions(): array
    {
        return $this->http()->get('core_versions')->json();
    }

    /**
     * Supported devices info.
     */
    public function supportedDevices(?string $core_version = null): array
    {
        return $this->http()->get('supported_devices', array_filter([
            'core_version' => $core_version,
        ], fn ($v) => ! is_null($v)))->json();
    }

    /**
     * Downloadable voice libraries.
     */
    public function downloadableLibraries(): array
    {
        return $this->http()->get('downloadable_libraries')->json();
    }

    /**
     * Installed voice libraries.
     */
    public function installedLibraries(): array
    {
        return $this->http()->get('installed_libraries')->json();
    }

    /**
     * Connect multiple base64-encoded WAV data into one WAV file.
     *
     * @param  array<string>  $waves  Base64-encoded WAV strings
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function connectWaves(array $waves): TalkResponse
    {
        $body = $this->http()->post('connect_waves', $waves)->throw()->body();

        return new TalkResponse($body);
    }

    /**
     * Check which styles can be morphing targets for the given base style IDs.
     *
     * @param  array<int>  $style_ids
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function morphableTargets(array $style_ids, ?string $core_version = null): array
    {
        return $this->http()->withQueryParameters(array_filter([
            'core_version' => $core_version,
        ], fn ($v) => ! is_null($v)))
            ->post('morphable_targets', $style_ids)
            ->throw()
            ->json();
    }

    /**
     * Synthesize morphed audio between two speaker styles.
     *
     * @param  float  $morph_rate  0.0 = base speaker, 1.0 = target speaker
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function morphing(array $audio_query, int|string $base_speaker, int|string $target_speaker, float $morph_rate, ?string $core_version = null): TalkResponse
    {
        $body = $this->http()->withQueryParameters(array_filter([
            'base_speaker' => $base_speaker,
            'target_speaker' => $target_speaker,
            'morph_rate' => $morph_rate,
            'core_version' => $core_version,
        ], fn ($v) => ! is_null($v)))
            ->post('synthesis_morphing', $audio_query)
            ->throw()
            ->body();

        return new TalkResponse($body);
    }
}
