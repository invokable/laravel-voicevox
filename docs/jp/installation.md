# インストールと設定

## 要件

- PHP 8.3+
- Laravel 12.x+
- FFI（クライアント以外の機能には FFI を有効にした PHP が必要です）

Laravel Cloud をはじめ一般的な Web サーバーではほとんどが FFI を無効にしているため、このパッケージは **ローカル CLI** での利用を前提にしています。

## インストール

### クライアントのみ

公式 VOICEVOX エンジンに HTTP でアクセスするクライアント機能だけを使う場合は FFI 不要です。

```shell
composer require revolution/laravel-voicevox
```

### クライアント + ネイティブ（コア）

ネイティブモードやエンジン API を使うには `voicevox-core-php` も一緒にインストールします。

```shell
composer require revolution/laravel-voicevox revolution/voicevox-core-php
```

## VOICEVOX CORE 動的ライブラリのセットアップ

FFI が必要な機能（ネイティブモード・エンジン API）を使うには、VOICEVOX CORE 動的ライブラリをローカルにインストールする必要があります。  
[voicevox-core-php の README](https://github.com/invokable/voicevox-core-php/blob/main/README_jp.md) を参考にインストールしてください。

## 設定

`config/voicevox.php` を公開します。

```shell
php artisan vendor:publish --tag="voicevox-config"
```

### 主な設定項目

```php
// config/voicevox.php

return [
    'client' => [
        // 公式 VOICEVOX エンジンの URL
        'url' => env('VOICEVOX_URL', 'http://127.0.0.1:50021'),

        // エンジン API に渡すコアバージョン（通常は null のままで OK）
        'core_version' => env('VOICEVOX_CLIENT_CORE_VERSION'),
    ],

    'core' => [
        // voicevox_core ディレクトリへのフルパス
        'path' => env('VOICEVOX_CORE_PATH'),

        // OpenJTalk 辞書のパス（voicevox_core 内の相対パス）
        'dict' => env('VOICEVOX_CORE_DICT_PATH', 'dict/open_jtalk_dic_utf_8-1.11'),

        // 音声モデルのパス（voicevox_core 内の相対パス）
        'models' => env('VOICEVOX_CORE_MODELS_PATH', 'models/vvms'),

        // 起動時に読み込む .vvm ファイル（[] にすると全モデルを読み込む）
        'vvms' => ['0.vvm', '9.vvm', 's0.vvm'],
    ],

    'engine' => [
        // Laravel 版エンジン API を無効にする場合は true
        'disabled' => env('VOICEVOX_ENGINE_DISABLED', false),

        // 非対応エンドポイントのフォールバック先 URL
        'fallback_url' => env('VOICEVOX_ENGINE_FALLBACK_URL', 'http://127.0.0.1:50021'),
    ],
];
```

### 最低限必要な .env 設定

**クライアントモードのみ使う場合**

公式エンジンがデフォルトの `http://127.0.0.1:50021` で起動していれば `.env` の追加設定は不要です。

```dotenv
# エンジンの URL が異なる場合のみ設定
VOICEVOX_URL=http://127.0.0.1:50021
```

**ネイティブモード / エンジン API を使う場合**

```dotenv
VOICEVOX_CORE_PATH=/path/to/voicevox_core/
```

### FFI の有効化

ネイティブモードやエンジン API をローカルで使うには `php.ini` で FFI を有効にします。

```ini
ffi.enable=true
```

## エンジンリソースのインストール

`/speaker_info` や `/singer_info` などのキャラクター情報を提供するエンドポイントを使う場合は、リソースファイルを別途インストールします（500 MB 以上）。

```shell
php artisan voicevox:install
```

Orchestra Testbench の開発環境では次のコマンドを使います。

```shell
vendor/bin/testbench voicevox:install
```

## モードの概要

| モード | 説明 | FFI |
|--------|------|-----|
| **クライアント** | 公式 VOICEVOX エンジンに HTTP でアクセス | 不要 |
| **ネイティブ** | PHP FFI で VOICEVOX CORE を直接呼び出し | 必要 |
| **エンジン API** | Laravel アプリ内に VOICEVOX 互換 API を内蔵 | 必要 |
