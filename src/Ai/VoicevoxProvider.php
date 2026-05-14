<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Ai;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Ai\Contracts\Gateway\AudioGateway;
use Laravel\Ai\Contracts\Providers\AudioProvider;
use Laravel\Ai\Providers\Provider;

class VoicevoxProvider extends Provider implements AudioProvider
{
    use \Laravel\Ai\Providers\Concerns\GeneratesAudio;
    use \Laravel\Ai\Providers\Concerns\HasAudioGateway;

    public function __construct(
        protected array $config,
        protected Dispatcher $events,
    ) {}

    /**
     * Get the provider's audio gateway.
     */
    public function audioGateway(): AudioGateway
    {
        return $this->audioGateway ??= new VoicevoxGateway;
    }

    /**
     * Get the name of the default audio (TTS) model.
     * VOICEVOX does not use named models; this value is passed through for metadata only.
     */
    public function defaultAudioModel(): string
    {
        return $this->config['models']['audio']['default'] ?? 'voicevox';
    }

    /**
     * The API "key" field is re-used to hold the engine base URL.
     * Returns a sensible default so no configuration is required in simple setups.
     */
    public function providerCredentials(): array
    {
        return [
            'key' => $this->config['key'] ?? 'http://127.0.0.1:50021',
        ];
    }
}
