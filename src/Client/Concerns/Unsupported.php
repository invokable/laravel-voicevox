<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client\Concerns;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Revolution\Voicevox\VoicevoxResponse;

/**
 * エンジンAPIでフォールバックのみのメソッド。
 */
trait Unsupported
{
    /**
     * Synthesize multiple audio queries in batch.
     *
     * @param  array<array>  $audioQueries
     * @return VoicevoxResponse zip file containing the synthesized audio files
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
}
