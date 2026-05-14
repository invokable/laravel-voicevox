<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Ai;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Ai\Contracts\Gateway\AudioGateway;
use Laravel\Ai\Contracts\Providers\AudioProvider;
use Laravel\Ai\Providers\Concerns\GeneratesAudio;
use Laravel\Ai\Providers\Concerns\HasAudioGateway;
use Laravel\Ai\Providers\Provider;

class VoicevoxProvider extends Provider implements AudioProvider
{
    use GeneratesAudio;
    use HasAudioGateway;

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
}
