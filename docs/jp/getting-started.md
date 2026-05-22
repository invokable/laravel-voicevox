# はじめに

VOICEVOX for Laravel へようこそ！このガイドでは、パッケージの基本概念と使い方を説明します。

## VOICEVOX for Laravel とは

Laravel で VOICEVOX の音声合成機能を使うためのパッケージです。テキストから自然な日本語音声を生成できます。

### 3つの利用モード

このパッケージは、ニーズに応じて3つの利用モードを提供しています：

#### 1. クライアントモード（HTTP）

**特徴**:
- 公式 VOICEVOX エンジン（Docker/ローカル実行）に HTTP でアクセス
- FFI 不要で最も手軽
- Docker で簡単にエンジンを起動可能

**適している用途**:
- Docker が使える環境
- FFI が利用できない環境

**基本的な使い方**:
```php
use Revolution\Voicevox\Voicevox;

// 音声合成（ずんだもん、スタイルID: 1）
$response = Voicevox::talk('こんにちは、Laravel です。', id: 1)->generate(id: 1);
$response->storeAs('output.wav');

// 歌声合成
$response = Voicevox::song($score)->generate(id: 3001);
$response->storeAs('song.wav');
```

詳細: [クライアントモード - トーク](client-talk.md) / [クライアントモード - ソング](client-song.md)

#### 2. ネイティブモード（FFI）

**特徴**:
- VOICEVOX CORE を PHP の FFI で直接呼び出し
- HTTP 通信不要で高速
- 外部プロセス不要

**適している用途**:
- パフォーマンス重視
- オフライン環境
- CLI ツール組み込み

**基本的な使い方**:
```php
use function Revolution\Voicevox\talk;
use function Revolution\Voicevox\song;

// 音声合成（ヘルパー関数を使用）
$response = talk('こんにちは、Laravel です。', id: 1)->generate(id: 1);
$response->storeAs('output.wav');

// 歌声合成
$response = song($score)->generate(id: 3001);
$response->storeAs('song.wav');
```

詳細: [ネイティブモード - トーク](native-talk.md) / [ネイティブモード - ソング](native-song.md)

#### 3. エンジンAPIモード（Laravel が VOICEVOX エンジンになる）

**特徴**:
- Laravel アプリケーション自体が VOICEVOX エンジン API を提供
- 公式エンジンと互換性のある REST API
- 内部でネイティブモードを使用

**適している用途**:
- 複数のクライアントから利用
- マイクロサービスアーキテクチャ
- VOICEVOX 公式クライアントとの連携

**基本的な使い方**:

エンジンAPIルートはデフォルトで有効で `/audio_query`、`/synthesis` などの公式互換 API が使えます。

詳細: [エンジンAPIモード - トーク](engine-talk.md) / [エンジンAPIモード - ソング](engine-song.md)

### どのモードを選ぶべきか

| 要件 | おすすめモード |
|------|----------------|
| とりあえず試したい | **クライアントモード** |
| Docker が使える | **クライアントモード** |
| 高速化・オフライン環境 | **ネイティブモード** |
| API サーバーとして公開 | **エンジンAPIモード** |
| FFI が使えない | **クライアントモード** |

## クイックスタート

### 1. インストール

```bash
# クライアントモードのみ（FFI 不要）
composer require revolution/laravel-voicevox

# クライアント + ネイティブモード（FFI 必要）
composer require revolution/laravel-voicevox revolution/voicevox-core-php
```

詳細: [インストールと設定](installation.md)

### 2. Docker で公式エンジン起動（クライアントモードの場合）

```bash
docker pull voicevox/voicevox_engine:cpu-latest
docker run --rm -p '127.0.0.1:50021:50021' voicevox/voicevox_engine:cpu-latest
```

### 3. 音声合成を試す

#### クライアントモード

```php
use Revolution\Voicevox\Voicevox;

$response = Voicevox::talk('こんにちは、Laravel です。', id: 1)->generate(id: 1);
$response->storeAs('output.wav');
```

#### ネイティブモード

```php
use function Revolution\Voicevox\talk;

$response = talk('こんにちは、Laravel です。', id: 1)->generate(id: 1);
$response->storeAs('output.wav');
```

## 主要な機能

### 音声合成（トーク）

テキストから音声を生成します。

```php
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\Client\TalkAudioQuery;

// 基本的な音声合成
$response = Voicevox::talk('こんにちは', id: 1)->generate(id: 1);

// パラメータ調整
$response = Voicevox::talk('こんにちは', id: 1)
    ->tap(function (TalkAudioQuery $talk) {
        $talk->audioQuery['speedScale'] = 1.2;// 速度（1.0 = 標準）
        $talk->audioQuery['pitchScale'] = 0.05;// ピッチ
        $talk->audioQuery['intonationScale'] = 1.5;// イントネーション
        $talk->audioQuery['volumeScale'] = 1.0;// 音量
    })
    ->generate(id: 1);
```

### 歌声合成（ソング）

楽譜データから歌声を生成します。

```php
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\Song\Note;

$score = Score::make([
    Note::make(length: 15),
    Note::make(length: Note::len(ticks: 480, bpm: 120), lyric: 'ド', key: 60),
    Note::make(length: Note::len(480, 120), lyric: 'レ', key: 62),
    Note::make(length: Note::len(960, 120), lyric: 'ミ', key: 64),
    Note::make(length: 2),
]);

$response = Voicevox::song($score)->generate(id: 3001);
$response->storeAs('song.wav');
```

### ユーザー辞書

固有名詞や専門用語の読み方を登録できます。

```php
use function Revolution\Voicevox\dict;

$uuid = dict()->add(
    surface: 'Laravel',
    pronunciation: 'ララベル',
    accent_type: 3,
);
```

詳細: [ユーザー辞書](user-dict.md)

### プリセット

よく使う音声パラメータをプリセットとして保存できます。

```php
use function Revolution\Voicevox\preset;

$id = preset()->add([
    'id' => 0, // 0 を指定すると自動採番される
    'name' => 'ゆっくり丁寧',
    'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
    'style_id' => 1,
    'speedScale' => 0.8,
    'pitchScale' => 0.0,
    'intonationScale' => 1.2,
    'volumeScale' => 1.0,
    'prePhonemeLength' => 0.1,
    'postPhonemeLength' => 0.1,
]);
```

詳細: [プリセット](presets.md)

### Laravel AI SDK 連携

Laravel AI SDK を使った統一インターフェースで音声合成できます。

```php
use Laravel\Ai\Audio;

$audio = Audio::of('こんにちは、Laravel です。')
    ->voice('ずんだもん')
    ->generate();

$audio->storeAs('output.wav');
```

詳細: [AI SDK 連携](ai-sdk.md)

## 次のステップ

- [インストールと設定](installation.md) - 詳細なセットアップ手順
- [クライアントモード - トーク](client-talk.md) - HTTP クライアントでの音声合成
- [ネイティブモード - トーク](native-talk.md) - FFI を使った高速音声合成
- [エンジンAPIモード - トーク](engine-talk.md) - Laravel を VOICEVOX エンジンとして公開
- [AI SDK 連携](ai-sdk.md) - Laravel AI SDK を使った統一インターフェース
- [ユーザー辞書](user-dict.md) - 固有名詞の読み方登録
- [プリセット](presets.md) - 音声パラメータの保存と再利用
