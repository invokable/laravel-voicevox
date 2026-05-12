<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Talk;

use Revolution\Voicevox\Core\Enums\AccelerationMode;
use Revolution\Voicevox\Core\Onnxruntime;
use Revolution\Voicevox\Core\OpenJtalk;
use Revolution\Voicevox\Core\Synthesizer;
use Revolution\Voicevox\Core\VoiceModelFile;

class Talk
{
    protected Synthesizer $synthesizer;

    public function __construct()
    {
        // 仮実装

        $voicevoxCoreDir = config('voicevox.core.path');
        $onnxruntimeFilename = $voicevoxCoreDir.'/onnxruntime/lib/'.Onnxruntime::libVersionedFilename();
        $dictDir = $voicevoxCoreDir.'/dict/open_jtalk_dic_utf_8-1.11';
        $vvmPath = $voicevoxCoreDir.'/models/vvms/0.vvm';

        // 初期化
        $onnxruntime = Onnxruntime::loadOnce($onnxruntimeFilename);
        $openJtalk = new OpenJtalk($dictDir);
        $this->synthesizer = new Synthesizer($onnxruntime, $openJtalk, AccelerationMode::Auto);

        // 音声モデルの読み込み
        $model = VoiceModelFile::open($vvmPath);
        $this->synthesizer->loadVoiceModel($model);
    }

    public static function make(string $text, int|string $id = 1): TalkAudioQuery
    {
        return (new self)->talk($text, $id);
    }

    public function talk(string $text, int|string $id = 1): TalkAudioQuery
    {
        $audio_query = json_decode($this->synthesizer->createAudioQuery($text, $id), true);

        return new TalkAudioQuery($this->synthesizer, $audio_query, $id);
    }
}
