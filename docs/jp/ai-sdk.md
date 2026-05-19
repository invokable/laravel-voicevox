# Laravel AI SDK 連携

VOICEVOX for Laravel は [Laravel AI SDK](https://github.com/laravel/ai) と統合されており、`Audio` ファサードから音声を生成できます。

クライアントモード（`voicevox-client`）とネイティブモード（`voicevox`）の2つのドライバーが用意されています。

---

## クライアントドライバー（`voicevox-client`）

公式 VOICEVOX エンジンに HTTP リクエストを送信して音声を生成します。FFI 不要です。

### 事前準備

Docker で公式 VOICEVOX エンジンを起動します。

```shell
docker pull voicevox/voicevox_engine:cpu-latest
docker run --rm -p '127.0.0.1:50021:50021' voicevox/voicevox_engine:cpu-latest
```

### 設定

`config/ai.php` に `voicevox-client` プロバイダーを追加します。  
`key` フィールドにエンジンの URL を指定します（デフォルト: `http://127.0.0.1:50021`）。

```php
// config/ai.php
'providers' => [
    'voicevox-client' => [
        'driver' => 'voicevox-client',
        'key' => env('VOICEVOX_URL', 'http://127.0.0.1:50021'),
    ],
],
```

### 使い方

```php
use Laravel\Ai\Audio;

$response = Audio::of('Laravelが好きなのだ')
    ->voice('ずんだもん')
    ->generate('voicevox-client');

Storage::put('talk.wav', $response->content());
```

---

## ネイティブドライバー（`voicevox`）

VOICEVOX CORE を直接呼び出して音声を生成します。FFI が必要です。`config/voicevox.php` の `core.vvms` で読み込んでいるモデルのみ使用できます。

### 設定

`config/ai.php` に `voicevox` プロバイダーを追加します。

```php
// config/ai.php
'providers' => [
    'voicevox' => [
        'driver' => 'voicevox',
    ],
],
```

### 使い方

```php
use Laravel\Ai\Audio;

$response = Audio::of('ネイティブで話すのだ')
    ->voice('ずんだもん')
    ->generate('voicevox');

Storage::put('talk.wav', $response->content());
```

---

## voice() に指定できる値

`voice()` には VOICEVOX のスタイル ID（数値文字列）またはキャラクター名エイリアスを指定します。  
`voice()` を省略した場合は `default-female`（ID: 10）が使われます。

### 名前エイリアス一覧

| エイリアス | スタイル ID | キャラクター |
|---|---|---|
| `ずんだもん` | 1 | ずんだもん（あまあま） |
| `ずんだもん/あまあま` | 1 | ずんだもん（あまあま） |
| `ずんだもん/ノーマル` | 3 | ずんだもん（ノーマル） |
| `ずんだもん/セクシー` | 5 | ずんだもん（セクシー） |
| `ずんだもん/ツンツン` | 7 | ずんだもん（ツンツン） |
| `ずんだもん/ささやき` | 22 | ずんだもん（ささやき） |
| `四国めたん/あまあま` | 0 | 四国めたん（あまあま） |
| `四国めたん` | 2 | 四国めたん（ノーマル） |
| `四国めたん/ノーマル` | 2 | 四国めたん（ノーマル） |
| `四国めたん/セクシー` | 4 | 四国めたん（セクシー） |
| `四国めたん/ツンツン` | 6 | 四国めたん（ツンツン） |
| `春日部つむぎ` | 8 | 春日部つむぎ（ノーマル） |
| `波音リツ` | 9 | 波音リツ（ノーマル） |
| `雨晴はう` | 10 | 雨晴はう（ノーマル） |
| `玄野武宏` | 11 | 玄野武宏（ノーマル） |
| `白上虎太郎` | 12 | 白上虎太郎（ふつう） |
| `青山龍星` | 13 | 青山龍星（ノーマル） |
| `冥鳴ひまり` | 14 | 冥鳴ひまり（ノーマル） |
| `九州そら` | 16 | 九州そら（ノーマル） |
| `default-female` | 10 | 雨晴はう（ノーマル） |
| `default-male` | 12 | 白上虎太郎（ふつう） |

上記以外の値は整数にキャストして生のスタイル ID として使用されます。

```php
// 数値文字列でスタイル ID を直接指定
Audio::of('テスト')->voice('3')->generate('voicevox-client');
```

利用可能なスピーカーとスタイル ID の一覧は [voicevox_vvm](https://github.com/VOICEVOX/voicevox_vvm) もしくはクライアントの `speakers()` で確認できます。

```php
use Revolution\Voicevox\Voicevox;

$speakers = Voicevox::speakers();
```

## エージェント

ネイティブモードでは`enable_katakana_english`に対応していないので事前に英語カタカナ変換するLaravel AI SDKエージェントを用意しています。好みのAIプロバイダーで使用してください。

### KanalizerAgent

英語だけをカタカナに変換します。VOICEVOX公式の [kanalizer](https://github.com/VOICEVOX/kanalizer) の代用です。

```php
use Revolution\Voicevox\Ai\Agents\KanalizerAgent;
use function Revolution\Voicevox\talk;

$kana = KanalizerAgent::make()->prompt('KanalizerAgentで英語をカタカナに変換するのだ');
// カナライザーエージェントで英語をカタカナに変換するのだ

// AI(LLM)による変換は確実ではないので直接talk()に渡すよりは人間のチェックを挟んでください

$response = talk($kana['kana'] ?? $kana->text, id: 1)->generate(id: 1);

$response->storeAs('native', 'kanalizer.wav');
```
