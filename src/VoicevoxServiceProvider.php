<?php

declare(strict_types=1);

namespace Revolution\Voicevox;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Laravel\Ai\Ai;
use Revolution\Voicevox\Ai\VoicevoxClientProvider;
use Revolution\Voicevox\Ai\VoicevoxProvider;
use Revolution\Voicevox\Client\VoicevoxClient;
use Revolution\Voicevox\Core\Enums\AccelerationMode;
use Revolution\Voicevox\Core\Onnxruntime;
use Revolution\Voicevox\Core\OpenJtalk;
use Revolution\Voicevox\Core\Synthesizer;
use Revolution\Voicevox\Core\VoiceModelFile;

class VoicevoxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/voicevox.php', 'voicevox',
        );

        $this->app->scoped(VoicevoxClient::class, VoicevoxClient::class);

        $this->configureSynthesizer();
    }

    protected function configureSynthesizer(): void
    {
        $this->app->scoped(Synthesizer::class, function () {
            $voicevoxCoreDir = rtrim(config('voicevox.core.path'), '/').'/';

            $onnxruntimeFilename = $voicevoxCoreDir.'onnxruntime/lib/'.Onnxruntime::libVersionedFilename();
            $dictDir = $voicevoxCoreDir.trim(config('voicevox.core.dict', 'dict/open_jtalk_dic_utf_8-1.11'), '/').'/';
            $modelDir = $voicevoxCoreDir.trim(config('voicevox.core.models', 'models/vvms'), '/').'/';

            // 初期化
            $onnxruntime = Onnxruntime::loadOnce($onnxruntimeFilename);
            $openJtalk = new OpenJtalk($dictDir);
            $synthesizer = new Synthesizer($onnxruntime, $openJtalk, AccelerationMode::Auto);

            // 音声モデルの読み込み
            $load_vvms = config('voicevox.core.vvms');
            $vvms = File::files($modelDir);
            foreach ($vvms as $vvm) {
                if (empty($load_vvms) || in_array(
                    $vvm->getFilename(),
                    $load_vvms,
                    true,
                )) {
                    $model = VoiceModelFile::open($vvm->getRealPath());
                    $synthesizer->loadVoiceModel($model);
                }
            }

            return $synthesizer;
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/voicevox.php' => config_path('voicevox.php'),
        ], 'voicevox-config');

        Ai::extend('voicevox', function ($app, array $config) {
            return new VoicevoxProvider($config, $app->make(Dispatcher::class));
        });

        Ai::extend('voicevox-client', function ($app, array $config) {
            return new VoicevoxClientProvider($config, $app->make(Dispatcher::class));
        });
    }
}
