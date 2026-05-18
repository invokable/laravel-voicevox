# VOICEVOX for Laravel

[![tests](https://github.com/invokable/laravel-voicevox/actions/workflows/tests.yml/badge.svg)](https://github.com/invokable/laravel-voicevox/actions/workflows/tests.yml)
[![linter](https://github.com/invokable/laravel-voicevox/actions/workflows/lint.yml/badge.svg)](https://github.com/invokable/laravel-voicevox/actions/workflows/lint.yml)
[![Maintainability](https://qlty.sh/badges/cc6e0ee3-e221-4c06-90be-4fd87b79310e/maintainability.svg)](https://qlty.sh/gh/invokable/projects/laravel-voicevox)
[![Code Coverage](https://qlty.sh/badges/cc6e0ee3-e221-4c06-90be-4fd87b79310e/coverage.svg)](https://qlty.sh/gh/invokable/projects/laravel-voicevox)
[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/invokable/laravel-voicevox)

Work In Progress.

## Overview

| Feature          | Supported | Description                                                                                                         |
|------------------|-----------|---------------------------------------------------------------------------------------------------------------------|
| VOICEVOX Client  | ✅         | 公式のVOICEVOXエンジンAPIにアクセスするクライアント。FFIなしでも動きます。                                                                        | 
| VOICEVOX Core    | ✅         | [voicevox-core-php](https://github.com/invokable/voicevox-core-php) VOICEVOX COREの動的ライブラリをFFIでラップしPHPネイティブのように使えます。 |
| Laravel style    | ✅         | Laravelスタイルでvoicevox-core-phpを扱う。                                                                                   |
| Laravel AI SDK統合 | ✅         | Laravel AI SDKのAudioから使う。クライアント版とネイティブ版、両方で対応しています。                                                                 |
| VOICEVOX Engine  | ⚠️        | PHP版コアを利用したLaravel版エンジンAPI。技術的に移植不可能な機能は公式エンジンにフォールバックします。                                                          |
| VOICEVOX Editor  | ❌         |                                                                                                                     |

## Requirements

- PHP 8.3+
- Laravel 12.x+
- FFI: クライアント以外にはFFIを有効にしたPHPが必要です。Laravel Cloudを始め一般的なWebサーバーではほとんどが無効にされているのでこのパッケージはローカルCLIでの利用を前提にしています。

## Installation

FFIが使えない環境用に`voicevox-core-php`は分けてインストールします。

```shell
composer require revolution/laravel-voicevox revolution/voicevox-core-php
```

`laravel-voicevox`だけインストールしてクライアント機能を使うこともできます。

```shell
composer require revolution/laravel-voicevox
```

## VOICEVOX CORE 動的ライブラリのセットアップ

FFIが必要な機能を使うには、[voicevox-core-phpのREADME](https://github.com/invokable/voicevox-core-php/blob/main/README_jp.md) を参考にVOICEVOX COREをインストールしてください。

## Configuration

configファイルを公開。`config/voicevox.php`

```shell
php artisan vendor:publish --tag="voicevox-config"
```

コア機能を使う場合は`.env`でパスを設定します。

```dotenv
VOICEVOX_CORE_PATH=/.../.local/voicevox_core/
```

## Usage

### Client mode

クライアントモードはVoicevox Facadeから使います。

### Native mode

ネイティブモードは`talk()`や`song()`のヘルパーから使います。

テキスト音声合成。ネイティブ版では`enable_katakana_english`がないので事前にAI(LLM)で英語からカタカナに変換するなどして運用でカバーしてください。

```php
use Revolution\Voicevox\Talk\TalkAudioQuery
use function Revolution\Voicevox\talk;

$response = talk('ララベルが好きなのだ', id: 1)
            ->tap(function (TalkAudioQuery $talk) {
               // tapで調整できます
               $talk->audioQuery['speedScale'] = 1.2;
            })->generate(id: 1);

$response->storeAs('talk.wav');
```

歌声合成はScoreを作成してから合成します。`length`は **フレーム長** ですが分かりにくのでMIDIに慣れた方向けに`Note::len()`ヘルパーも用意しています。四分音符一つを480としてBGMと共に指定すればフレーム長が計算されます。

```php
use Revolution\Voicevox\Song\Note;
use Revolution\Voicevox\Song\Score;
use function Revolution\Voicevox\song;

$score = Score::make([
    Note::make(length: 15), // 1音目は必ず休符
    Note::make(length: Note::len(ticks: 480, bpm: 120), lyric: 'ド', key: 60),
    Note::make(length: Note::len(480, 120), lyric: 'レ', key: 62),
    Note::make(length: Note::len(960, 120), lyric: 'ミ', key: 64),
    Note::make(length: 2), // 最後も短く無音を入れるとよい
]);

$response = song($score, teacher: 6000)->generate(id: 3001);

$response->storeAs('song.wav');
```

## Documentation

## 利用規約

VOICEVOXと音声ライブラリの利用規約に従う必要があります。

## Licence

MIT
