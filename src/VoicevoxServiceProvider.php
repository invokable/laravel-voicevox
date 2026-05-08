<?php

namespace Revolution\Voicevox;

use Illuminate\Support\ServiceProvider;
use Revolution\Voicevox\Client\VoicevoxClient;

class VoicevoxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/voicevox.php', 'voicevox',
        );

        $this->app->scoped(VoicevoxClient::class, VoicevoxClient::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/voicevox.php' => config_path('voicevox.php'),
        ], 'voicevox-config');
    }
}
