<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client\Concerns;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Revolution\Voicevox\Client\TalkAudioQuery;

trait HasPreset
{
    /**
     * Create Audio Query from Preset.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function talkFromPreset(string $text, int $presetId, bool $enableKatakanaEnglish = true): TalkAudioQuery
    {
        $audioQuery = $this->audioQueryFromPreset($text, $presetId, $enableKatakanaEnglish);

        return new TalkAudioQuery($audioQuery);
    }

    /**
     * Create Audio Query from Preset.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function audioQueryFromPreset(string $text, int $presetId, bool $enableKatakanaEnglish = true): array
    {
        $response = $this->http()->withQueryParameters(array_filter([
            'text' => $text,
            'preset_id' => $presetId,
            'enable_katakana_english' => $enableKatakanaEnglish,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('audio_query_from_preset')
            ->throw();

        return $response->json();
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
}
