<?php

declare(strict_types=1);

return [
    'client' => [
        /**
         * VOICEVOXエンジンAPIのURL。
         */
        'url' => env('VOICEVOX_URL', 'http://127.0.0.1:50021'),

        /**
         * エンジンAPIで指定するコアバージョン。
         */
        'core_version' => env('VOICEVOX_CLIENT_CORE_VERSION'),
    ],

    'core' => [
        /**
         * voicevox_coreまでのフルパス。
         *
         * /Users/.../.local/voicevox_core/
         */
        'path' => env('VOICEVOX_CORE_PATH'),

        /**
         * voicevox_core内のOpenJtalkのパス。
         *
         * dict/open_jtalk_dic_utf_8-1.11
         */
        'dict' => env('VOICEVOX_CORE_DICT_PATH', 'dict/open_jtalk_dic_utf_8-1.11'),

        /**
         * voicevox_core内のmodelsのパス。
         *
         * models/vvms
         */
        'models' => env('VOICEVOX_CORE_MODELS_PATH', 'models/vvms'),

        /**
         * 起動時に読み込むモデルの配列。[]なら全モデルを読み込み、ただしかなり遅くなるのでデフォルトは0.vvmと歌声用のs0.vvmとAI SDKのデフォルト男性音声用の9.vvmのみ。
         *
         * ['0.vvm', '1.vvm']
         */
        'vvms' => ['0.vvm', '9.vvm', 's0.vvm'],
    ],

    'engine' => [
        'disabled' => env('VOICEVOX_ENGINE_DISABLED', false),

        /**
         * 対応してないAPIは公式エンジンに移譲。
         */
        'fallback_url' => env('VOICEVOX_ENGINE_FALLBACK_URL', 'http://127.0.0.1:50021'),

        /**
         * 公式エンジンに移譲したけど起動してない時のエラーメッセージ。
         */
        'fallback_error' => 'The Laravel version of the engine does not support this endpoint. Please use the official engine instead.',
    ],
];
