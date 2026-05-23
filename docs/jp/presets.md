# ネイティブモード：プリセット

プリセット機能を使うと、よく使う音声合成パラメータの組み合わせを保存して、簡単に再利用できます。話速・音高・抑揚などの設定をまとめて管理することで、一貫した音声品質を維持しやすくなります。

## 概要

Laravel VOICEVOX のプリセット機能は、VOICEVOX Core を利用したネイティブ実装です。プリセットデータは JSON 形式で `storage/voicevox/presets.json` に永続化されます。

公式 VOICEVOX エンジンとは独立したストレージを使用するため、Laravel 側で作成したプリセットは公式エンジン側には反映されません（その逆も同様です）。

## 設定

デフォルトでは `storage/voicevox/presets.json` に保存されます。別のパスを使いたい場合は `config/voicevox.php` で変更できます。

```php
// config/voicevox.php

return [
    'core' => [
        'presets' => storage_path('voicevox/presets.json'),
    ],
];
```

## プリセットの構造

プリセットは以下の構造を持つ配列です。

```php
[
    'id' => 1,                // プリセット ID（整数、自動採番）
    'name' => 'ゆっくり',      // プリセット名
    'speaker_uuid' => 'uuid', // スピーカー UUID（文字列）
    'style_id' => 1,          // スタイル ID（整数）
    'speedScale' => 0.8,      // 話速（0.5 〜 2.0）
    'pitchScale' => 0.0,      // 音高（-0.15 〜 0.15）
    'intonationScale' => 1.0, // 抑揚（0.0 〜 2.0）
    'volumeScale' => 1.0,     // 音量（0.0 〜 2.0）
    'prePhonemeLength' => 0.1,// 開始無音（0.0 〜 1.5）
    'postPhonemeLength' => 0.1,// 終了無音（0.0 〜 1.5）
]
```

## 基本的な使い方

### プリセットの作成

`preset()` ヘルパーの `add()` メソッドでプリセットを作成できます。

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

// 自動採番された ID が返される
echo $id; // 例: 1
```

### 全プリセットの取得

登録されているすべてのプリセットを取得できます。

```php
use function Revolution\Voicevox\preset;

$presets = preset()->all();

/*
[
    [
        "id" => 1,
        "name" => "ゆっくり丁寧",
        "speaker_uuid" => "7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff",
        "style_id" => 1,
        "speedScale" => 0.8,
        ...
    ],
    ...
]
*/
```

### プリセットの検索

ID を指定してプリセットを取得できます。

```php
use function Revolution\Voicevox\preset;

$preset = preset()->find(1);

if ($preset !== null) {
    echo $preset['name']; // "ゆっくり丁寧"
}
```

### プリセットの更新

既存のプリセットを更新できます。

```php
use function Revolution\Voicevox\preset;

preset()->update([
    'id' => 1,
    'name' => 'ゆっくりはっきり',
    'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
    'style_id' => 1,
    'speedScale' => 0.7,
    'pitchScale' => 0.0,
    'intonationScale' => 1.5,
    'volumeScale' => 1.0,
    'prePhonemeLength' => 0.1,
    'postPhonemeLength' => 0.1,
]);
```

### プリセットの削除

プリセットを削除できます。

```php
use function Revolution\Voicevox\preset;

preset()->delete(1);
```

## プリセットを使った音声合成

プリセットを使って音声合成を行うには、`talk()`ヘルパーかEngine API の `/audio_query_from_preset` エンドポイントを利用します。

### talk() ヘルパー

`talk(preset:)`にプリセットIDを指定します。この時通常のスタイルIDは無視されます。

```php
use function Revolution\Voicevox\talk;

$response = talk('プリセットを使うのだ', preset: 1)->generate(id: 1);
```

プリセット機能の実際の動作はデフォルトAudioQueryがプリセットで指定した値に変わるだけなので通常の音声合成とほとんど同じです。

### Engine API 経由でのアクセス

Laravel VOICEVOX のエンジン API を起動している場合、HTTP 経由でプリセットを操作できます。

#### 全プリセットの取得

```bash
curl http://localhost:50513/presets
```

#### プリセットの追加

```bash
curl -X POST http://localhost:50513/add_preset \
  -H "Content-Type: application/json" \
  -d '{
    "id": 0,
    "name": "ゆっくり丁寧",
    "speaker_uuid": "7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff",
    "style_id": 1,
    "speedScale": 0.8,
    "pitchScale": 0.0,
    "intonationScale": 1.2,
    "volumeScale": 1.0,
    "prePhonemeLength": 0.1,
    "postPhonemeLength": 0.1
  }'
```

#### プリセットの更新

```bash
curl -X POST http://localhost:50513/update_preset \
  -H "Content-Type: application/json" \
  -d '{
    "id": 1,
    "name": "ゆっくりはっきり",
    "speaker_uuid": "7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff",
    "style_id": 1,
    "speedScale": 0.7,
    "pitchScale": 0.0,
    "intonationScale": 1.5,
    "volumeScale": 1.0,
    "prePhonemeLength": 0.1,
    "postPhonemeLength": 0.1
  }'
```

#### プリセットの削除

```bash
curl -X POST http://localhost:50513/delete_preset \
  -H "Content-Type: application/json" \
  -d '{"id": 1}'
```

#### プリセットを使った音声クエリの生成

```bash
curl -X POST "http://localhost:50513/audio_query_from_preset?text=こんにちは&preset_id=1"
```

## 実用例

### シナリオ別プリセット

#### ナレーション用

```php
use function Revolution\Voicevox\preset;

preset()->add([
    'id' => 0,
    'name' => 'ナレーション',
    'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
    'style_id' => 1,
    'speedScale' => 1.0,
    'pitchScale' => -0.05,
    'intonationScale' => 0.8,
    'volumeScale' => 1.0,
    'prePhonemeLength' => 0.15,
    'postPhonemeLength' => 0.15,
]);
```

#### 感情表現豊かな読み上げ

```php
preset()->add([
    'id' => 0,
    'name' => '感情豊か',
    'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
    'style_id' => 2,
    'speedScale' => 1.1,
    'pitchScale' => 0.1,
    'intonationScale' => 1.5,
    'volumeScale' => 1.2,
    'prePhonemeLength' => 0.05,
    'postPhonemeLength' => 0.05,
]);
```

#### アナウンス用

```php
preset()->add([
    'id' => 0,
    'name' => 'アナウンス',
    'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
    'style_id' => 1,
    'speedScale' => 0.9,
    'pitchScale' => 0.0,
    'intonationScale' => 1.0,
    'volumeScale' => 1.1,
    'prePhonemeLength' => 0.2,
    'postPhonemeLength' => 0.2,
]);
```

#### 早口トーク用

```php
preset()->add([
    'id' => 0,
    'name' => '早口',
    'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
    'style_id' => 3,
    'speedScale' => 1.5,
    'pitchScale' => 0.05,
    'intonationScale' => 1.3,
    'volumeScale' => 1.0,
    'prePhonemeLength' => 0.05,
    'postPhonemeLength' => 0.05,
]);
```

### キャラクター別プリセット

複数のキャラクター用にプリセットを作成して管理できます。

```php
use function Revolution\Voicevox\preset;

// ずんだもん用
preset()->add([
    'id' => 0,
    'name' => 'ずんだもん標準',
    'speaker_uuid' => '388f246b-8c41-4ac1-8e2d-5d79f3ff56d9',
    'style_id' => 3, // ノーマル
    'speedScale' => 1.0,
    'pitchScale' => 0.0,
    'intonationScale' => 1.0,
    'volumeScale' => 1.0,
    'prePhonemeLength' => 0.1,
    'postPhonemeLength' => 0.1,
]);

// 四国めたん用
preset()->add([
    'id' => 0,
    'name' => '四国めたん標準',
    'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
    'style_id' => 2, // ノーマル
    'speedScale' => 1.0,
    'pitchScale' => 0.0,
    'intonationScale' => 1.0,
    'volumeScale' => 1.0,
    'prePhonemeLength' => 0.1,
    'postPhonemeLength' => 0.1,
]);
```

## パラメータ詳細

### speedScale（話速）

- **範囲**: 0.5 〜 2.0
- **デフォルト**: 1.0
- **効果**: 読み上げ速度を調整します
  - 1.0 より小さい: ゆっくり
  - 1.0 より大きい: 速く

### pitchScale（音高）

- **範囲**: -0.15 〜 0.15
- **デフォルト**: 0.0
- **効果**: 声の高さを調整します
  - マイナス: 低く
  - プラス: 高く

### intonationScale（抑揚）

- **範囲**: 0.0 〜 2.0
- **デフォルト**: 1.0
- **効果**: イントネーションの強弱を調整します
  - 0.0: 平坦（棒読み）
  - 1.0: 標準
  - 2.0: 抑揚が強い

### volumeScale（音量）

- **範囲**: 0.0 〜 2.0
- **デフォルト**: 1.0
- **効果**: 音量を調整します
  - 0.0: 無音
  - 1.0: 標準
  - 2.0: 大きい

### prePhonemeLength（開始無音）

- **範囲**: 0.0 〜 1.5（秒）
- **デフォルト**: 0.1
- **効果**: 音声の開始前の無音時間

### postPhonemeLength（終了無音）

- **範囲**: 0.0 〜 1.5（秒）
- **デフォルト**: 0.1
- **効果**: 音声の終了後の無音時間

## 注意事項

- **公式エンジンとは独立**: Laravel 版のプリセットと公式 VOICEVOX エンジンのプリセットは別々に管理されます。
- **自動保存**: プリセットの追加・更新・削除は即座に JSON ファイルに保存されます。
- **ID の自動採番**: プリセット ID に `0` を指定すると、自動的に未使用の ID が割り当てられます。
- **speaker_uuid と style_id**: プリセットには speaker_uuid（UUID 文字列）と style_id（整数）の両方を含めてください。

## トラブルシューティング

### プリセットが保存されない

1. ストレージディレクトリの書き込み権限を確認してください
2. `storage/voicevox/presets.json` が正しく作成されているか確認してください

### プリセットファイルの場所がわからない

```php
$path = config('voicevox.core.presets');
echo $path;
```

### プリセットをリセットしたい

プリセットファイルを削除すると、次回から空のプリセットリストが作成されます。

```bash
rm storage/voicevox/presets.json
```

または PHP から：

```php
use Illuminate\Support\Facades\Storage;

$path = config('voicevox.core.presets');
if (file_exists($path)) {
    unlink($path);
}
```

### speaker_uuid がわからない

スピーカー一覧から UUID を取得できます。

```php
use Revolution\Voicevox\Voicevox;

$speakers = Voicevox::speakers();

foreach ($speakers as $speaker) {
    echo $speaker['name'] . ': ' . $speaker['speaker_uuid'] . PHP_EOL;
}
```

または Engine API 経由で：

```bash
curl http://localhost:8000/speakers
```
