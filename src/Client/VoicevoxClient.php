<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\VoicevoxResponse;

class VoicevoxClient
{
    use WithHttp;

    /**
     * Create a talk.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function talk(string $text, int|string $id = 1, bool $enableKatakanaEnglish = true): TalkAudioQuery
    {
        $audioQuery = $this->audioQuery($text, $id, $enableKatakanaEnglish);

        return new TalkAudioQuery(audioQuery: $audioQuery, id: $id);
    }

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
    public function addWord(string $surface, string $pronunciation, int $accentType, ?string $wordType = null, ?int $priority = null): string
    {
        return $this->http()->withQueryParameters(array_filter([
            'surface' => $surface,
            'pronunciation' => $pronunciation,
            'accent_type' => $accentType,
            'word_type' => $wordType,
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
    public function updateWord(string $wordUuid, string $surface, string $pronunciation, int $accentType, ?string $wordType = null, ?int $priority = null): void
    {
        $this->http()->withQueryParameters(array_filter([
            'surface' => $surface,
            'pronunciation' => $pronunciation,
            'accent_type' => $accentType,
            'word_type' => $wordType,
            'priority' => $priority,
        ], fn ($v) => ! is_null($v)))
            ->put("user_dict_word/{$wordUuid}")
            ->throw();
    }

    /**
     * Delete a word from User Dictionary.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function deleteWord(string $wordUuid): void
    {
        $this->http()->delete("user_dict_word/{$wordUuid}")->throw();
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
    public function supportedDevices(): array
    {
        return $this->http()->get('supported_devices', array_filter([
            'core_version' => config('voicevox.client.core_version'),
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
    public function connectWaves(array $waves): VoicevoxResponse
    {
        $body = $this->http()
            ->post('connect_waves', $waves)
            ->throw()
            ->body();

        return new VoicevoxResponse($body);
    }

    /**
     * Check which styles can be morphing targets for the given base style IDs.
     *
     * @param  array<int>  $styleIds
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function morphableTargets(array $styleIds): array
    {
        return $this->http()->withQueryParameters(array_filter([
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('morphable_targets', $styleIds)
            ->throw()
            ->json();
    }

    /**
     * Synthesize morphed audio between two speaker styles.
     *
     * @param  float  $morphRate  0.0 = base speaker, 1.0 = target speaker
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function morphing(array $audioQuery, int|string $baseSpeaker, int|string $targetSpeaker, float $morphRate): VoicevoxResponse
    {
        $body = $this->http()->withQueryParameters(array_filter([
            'base_speaker' => $baseSpeaker,
            'target_speaker' => $targetSpeaker,
            'morph_rate' => $morphRate,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('synthesis_morphing', $audioQuery)
            ->throw()
            ->body();

        return new VoicevoxResponse($body);
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
     * Synthesize multiple audio queries in batch.
     *
     * @param  array<array>  $audioQueries
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function multiSynthesis(array $audioQueries, int|string $id, bool $interrogativeUpspeak = false): VoicevoxResponse
    {
        $body = $this->http()->withQueryParameters(array_filter([
            'speaker' => $id,
            'enable_interrogative_upspeak' => $interrogativeUpspeak,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('multi_synthesis', $audioQueries)
            ->throw()
            ->body();

        return new VoicevoxResponse($body);
    }

    /**
     * Validate text against AquesTalk notation.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function validateKana(string $text): bool
    {
        return $this->http()->withQueryParameters(['text' => $text])
            ->post('validate_kana')
            ->throw()
            ->json();
    }

    /**
     * Initialize a speaker (load voice model into memory).
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function initializeSpeaker(int|string $id, bool $skipReinit = false): void
    {
        $this->http()->withQueryParameters(array_filter([
            'speaker' => $id,
            'skip_reinit' => $skipReinit,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))
            ->post('initialize_speaker')
            ->throw();
    }

    /**
     * Check if a speaker has been initialized.
     */
    public function isInitializedSpeaker(int|string $id): bool
    {
        return $this->http()->get('is_initialized_speaker', array_filter([
            'speaker' => $id,
            'core_version' => config('voicevox.client.core_version'),
        ], fn ($v) => ! is_null($v)))->json();
    }

    /**
     * Install a voice library by UUID.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function installLibrary(string $libraryUuid): void
    {
        $this->http()->post("install_library/{$libraryUuid}")->throw();
    }

    /**
     * Uninstall a voice library by UUID.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function uninstallLibrary(string $libraryUuid): void
    {
        $this->http()->post("uninstall_library/{$libraryUuid}")->throw();
    }

    /**
     * Get engine settings.
     */
    public function setting(): string
    {
        return $this->http()->get('setting')->body();
    }

    /**
     * Update engine settings.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function updateSetting(array $settings): void
    {
        $this->http()->asForm()->post('setting', $settings)->throw();
    }

    /**
     * Cancellable synthesis — same as synthesis but supports server-sent cancellation.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function cancellableSynthesis(array $audioQuery, int|string $id = 1, bool $enableInterrogativeUpspeak = true): string
    {
        $response = $this->http()
            ->accept('audio/wav')
            ->withQueryParameters(array_filter([
                'speaker' => $id,
                'enable_interrogative_upspeak' => $enableInterrogativeUpspeak,
                'core_version' => config('voicevox.client.core_version'),
            ], fn ($v) => ! is_null($v)))
            ->post('cancellable_synthesis', $audioQuery)
            ->throw();

        return $response->body();
    }

    /**
     * Health / portal check — returns the engine portal page HTML.
     */
    public function alive(): string
    {
        return $this->http()->get('/')->body();
    }

    /**
     * Create a song.
     *
     * @param  int|string  $teacher  typeがsingかsinging_teacherのスタイルID
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function song(array|Arrayable $score, int|string $teacher = 6000): SongAudioQuery
    {
        $score = $score instanceof Arrayable ? $score->toArray() : $score;

        $frameAudioQuery = $this->singFrameAudioQuery($score, $teacher);

        return new SongAudioQuery(score: $score, frameAudioQuery: $frameAudioQuery, teacher: $teacher);
    }

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
}
