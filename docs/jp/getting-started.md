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
- 開発環境での試用
- Docker が使える環境
- FFI が利用できない環境

**基本的な使い方**:
```php
use Revolution\Voicevox\Facades\Voicevox;

// 音声合成（ずんだもん、スタイルID: 3）
$response = Voicevox::talk('こんにちは、Laravel です。', speaker: 3);
$response->save('output.wav');

// 歌声合成
$response = Voicevox::song($score, speaker: 3);
$response->save('song.wav');
```

詳細: [クライアントモード - トーク](client-talk.md) / [クライアントモード - ソング](client-song.md)

#### 2. ネイティブモード（FFI）

**特徴**:
- VOICEVOX CORE を PHP の FFI で直接呼び出し
- HTTP 通信不要で高速
- 外部プロセス不要

**適している用途**:
- 本番環境でのパフォーマンス重視
- オフライン環境
- CLI ツール組み込み

**基本的な使い方**:
```php
use function Revolution\Voicevox\talk;
use function Revolution\Voicevox\song;

// 音声合成（ヘルパー関数を使用）
$response = talk('こんにちは、Laravel です。', speaker: 3);
$response->save('output.wav');

// 歌声合成
$response = song($score, speaker: 3);
$response->save('song.wav');
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
```php
// routes/api.php
use Revolution\Voicevox\Facades\VoicevoxEngine;

VoicevoxEngine::routes();
```

これで `/api/voicevox/audio_query`、`/api/voicevox/synthesis` などの公式互換 API が有効になります。

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
docker run -d -p 50021:50021 voicevox/voicevox_engine:cpu-ubuntu20.04-latest
```

### 3. 音声合成を試す

#### クライアントモード

```php
use Revolution\Voicevox\Facades\Voicevox;

$response = Voicevox::talk('こんにちは、Laravel です。', speaker: 3);
$response->save(storage_path('app/output.wav'));
```

#### ネイティブモード

```php
use function Revolution\Voicevox\talk;

$response = talk('こんにちは、Laravel です。', speaker: 3);
$response->save(storage_path('app/output.wav'));
```

## 主要な機能

### 音声合成（トーク）

テキストから音声を生成します。

```php
// 基本的な音声合成
$response = Voicevox::talk('こんにちは', speaker: 3);

// 感情パラメータ調整
$response = Voicevox::talk('こんにちは', speaker: 3, query: [
    'speedScale' => 1.2,      // 速度（1.0 = 標準）
    'pitchScale' => 0.05,     // ピッチ
    'intonationScale' => 1.5, // イントネーション
    'volumeScale' => 1.0,     // 音量
]);
```

### 歌声合成（ソング）

楽譜データから歌声を生成します。

```php
use Revolution\Voicevox\Data\Score;
use Revolution\Voicevox\Data\Note;

$score = Score::from([
    'notes' => [
        Note::from(['key' => 60, 'lyric' => 'ら']),
        Note::from(['key' => 62, 'lyric' => 'ら']),
    ],
]);

$response = Voicevox::song($score, speaker: 6000);
$response->save('song.wav');
```

### ユーザー辞書

固有名詞や専門用語の読み方を登録できます。

```php
use Revolution\Voicevox\Engine\NativeUserDict;

$dict = app(NativeUserDict::class);

$uuid = $dict->add([
    'surface' => 'Laravel',
    'pronunciation' => 'ララベル',
    'accent_type' => 3,
]);
```

詳細: [ユーザー辞書](user-dict.md)

### プリセット

よく使う音声パラメータをプリセットとして保存できます。

```php
use Revolution\Voicevox\Engine\NativePresetStore;

$store = app(NativePresetStore::class);

$id = $store->create([
    'name' => 'ナレーション用',
    'style_id' => 3,
    'speedScale' => 1.1,
    'pitchScale' => 0.0,
    'intonationScale' => 1.2,
    'volumeScale' => 1.0,
]);
```

詳細: [プリセット](presets.md)

### Laravel AI SDK 連携

Laravel AI SDK を使った統一インターフェースで音声合成できます。

```php
use Laravel\Ai\Facades\Audio;

$audio = Audio::of('こんにちは、Laravel です。')
    ->voice('ずんだもん')
    ->generate();

$audio->save('output.wav');
```

詳細: [AI SDK 連携](ai-sdk.md)

## キャラクターとスタイル ID

VOICEVOX では、キャラクターごとに複数のスタイル（話し方のバリエーション）があります。

### 主要なキャラクター

| キャラクター | スタイル | スタイルID |
|--------------|----------|------------|
| ずんだもん | ノーマル | 3 |
| ずんだもん | あまあま | 1 |
| ずんだもん | ツンツン | 7 |
| ずんだもん | セクシー | 5 |
| ずんだもん | ささやき | 22 |
| ずんだもん | ヒソヒソ | 38 |
| 四国めたん | ノーマル | 2 |
| 四国めたん | あまあま | 0 |
| 四国めたん | ツンツン | 6 |
| 四国めたん | セクシー | 4 |
| 四国めたん | ヒソヒソ | 37 |
| 春日部つむぎ | ノーマル | 8 |

### スタイル ID の確認方法

```php
use Revolution\Voicevox\Facades\Voicevox;

// 利用可能な全スピーカーを取得
$speakers = Voicevox::speakers();

foreach ($speakers as $speaker) {
    echo $speaker['name'] . "\n";
    foreach ($speaker['styles'] as $style) {
        echo "  - {$style['name']}: {$style['id']}\n";
    }
}
```

## 次のステップ

- [インストールと設定](installation.md) - 詳細なセットアップ手順
- [クライアントモード - トーク](client-talk.md) - HTTP クライアントでの音声合成
- [ネイティブモード - トーク](native-talk.md) - FFI を使った高速音声合成
- [エンジンAPIモード - トーク](engine-talk.md) - Laravel を VOICEVOX エンジンとして公開
- [AI SDK 連携](ai-sdk.md) - Laravel AI SDK を使った統一インターフェース
- [ユーザー辞書](user-dict.md) - 固有名詞の読み方登録
- [プリセット](presets.md) - 音声パラメータの保存と再利用

## トラブルシューティング

### FFI が使えない

クライアントモードのみを使用するか、FFI を有効にした PHP をインストールしてください。

```bash
# FFI の有効化確認
php -r "var_dump(extension_loaded('ffi'));"
```

### Docker エンジンに接続できない

Docker コンテナが起動しているか確認してください。

```bash
docker ps | grep voicevox
curl http://127.0.0.1:50021/version
```

### 音声が生成されない

- スタイル ID が正しいか確認
- VOICEVOX CORE ライブラリがインストールされているか確認
- ログを確認（`storage/logs/laravel.log`）

より詳しいトラブルシューティングについては、各機能の詳細ドキュメントを参照してください。

## ライセンスと利用規約

VOICEVOX の音声合成エンジンおよび各キャラクターには利用規約があります。商用利用の可否やクレジット表記の要否は、各キャラクターごとに異なります。

詳細は [VOICEVOX 公式サイト](https://voicevox.hiroshiba.jp/) を参照してください。

## リンク

- [GitHub リポジトリ](https://github.com/invokable/laravel-voicevox)
- [VOICEVOX 公式](https://voicevox.hiroshiba.jp/)
- [VOICEVOX Engine](https://github.com/VOICEVOX/voicevox_engine)
- [VOICEVOX Core](https://github.com/VOICEVOX/voicevox_core)
