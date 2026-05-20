# ネイティブモード：トーク（テキスト音声合成）

ネイティブモードは PHP FFI で VOICEVOX CORE を直接呼び出します。公式エンジンを起動せずに音声を生成できますが、FFI と VOICEVOX CORE 動的ライブラリのセットアップが必要です。

## 事前準備

1. `voicevox-core-php` をインストール済みであること
2. VOICEVOX CORE 動的ライブラリをセットアップ済みであること（[インストール手順](installation.md) を参照）
3. `.env` でコアのパスを設定済みであること

```dotenv
VOICEVOX_CORE_PATH=/path/to/voicevox_core/
```

## 基本的な使い方

`talk()` ヘルパー関数を使います。インターフェースはクライアントモードとほぼ同じです。

```php
use function Revolution\Voicevox\talk;

$response = talk('ネイティブ版なのだ', id: 1)->generate(id: 1);

$response->storeAs('native', 'talk.wav');
```

## スタイル ID の指定

`id` にはスタイル ID を指定します。読み込み済みの `.vvm` ファイルに含まれるスタイルのみ使用できます。

```php
use function Revolution\Voicevox\talk;

// id: 1 = ずんだもん（あまあま）
$response = talk('ずんだもんなのだ', id: 1)->generate(id: 1);
```

デフォルトで読み込まれるモデル（`config/voicevox.php` の `core.vvms`）に含まれるスタイルが使用可能です。

## tap() で音声パラメーターを調整

クライアントモードと同様に `tap()` でオーディオクエリーのパラメーターを調整できます。  
ただし `TalkAudioQuery` はクライアントモードとは別クラス（`Revolution\Voicevox\Talk\TalkAudioQuery`）です。

```php
use Revolution\Voicevox\Talk\TalkAudioQuery;
use function Revolution\Voicevox\talk;

$response = talk('速めに話すのだ', id: 1)
    ->tap(function (TalkAudioQuery $talk) {
        $talk->audioQuery['speedScale'] = 1.2;   // 話速（デフォルト: 1.0）
        $talk->audioQuery['pitchScale'] = 0.05;  // 音高（デフォルト: 0.0）
        $talk->audioQuery['intonationScale'] = 1.5; // 抑揚（デフォルト: 1.0）
        $talk->audioQuery['volumeScale'] = 1.0;  // 音量（デフォルト: 1.0）
    })
    ->generate(id: 1);

$response->storeAs('native', 'talk.wav');
```

## AquesTalk 風記法（カナ）からの合成

ネイティブモードでは `enable_katakana_english` がないため、英語が含まれるテキストはそのままでは正しく発音されません。  
代わりに AquesTalk 風記法のカタカナを直接指定する `kana()` 関数を使えます。

```php
use function Revolution\Voicevox\kana;

// AquesTalk 風記法カタカナ
$response = kana("ネイティブ'バンナ/ノダ'", id: 1)->generate(id: 1);

$response->storeAs('native', 'kana.wav');
```

### LLM によるカナ変換

Laravel AI SDK のエージェントを使って、日本語テキストを AquesTalk 風記法カタカナに変換することもできます。

```php
use Revolution\Voicevox\Ai\Agents\AquesTalkAgent;
use function Revolution\Voicevox\kana;

$result = AquesTalkAgent::make()->prompt('Laravelが好きです');
$response = kana($result->text, id: 1)->generate(id: 1);
```

## クライアントモードとの違い

| 項目 | クライアントモード | ネイティブモード |
|---|---|---|
| エンジン起動 | 必要（Docker 等） | 不要 |
| FFI | 不要 | 必要 |
| 英語自動カタカナ変換 | あり | なし |
| TalkAudioQuery クラス | `Revolution\Voicevox\Client\TalkAudioQuery` | `Revolution\Voicevox\Talk\TalkAudioQuery` |
| 使用する関数/Facade | `Voicevox::talk()` | `talk()` |

## メソッドチェーン全体の流れ

```
talk($text, id: $speakerId)
    → TalkAudioQuery（audio_query の結果を保持）
        → tap() でパラメーター調整（任意）
        → generate(id: $speakerId)
            → VoicevoxResponse（WAV 音声データ）
                → storeAs() / content()
```
