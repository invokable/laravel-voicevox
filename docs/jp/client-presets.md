# クライアントモード：プリセット

プリセット機能を使うと、よく使う音声合成パラメータの組み合わせを公式 VOICEVOX エンジンに保存して再利用できます。クライアントモードでは Laravel 側にプリセットを保存せず、接続先の公式エンジンが管理するプリセット API を利用します。

## 概要

クライアントモードのプリセットは `Voicevox` Facade から操作します。内部では公式 VOICEVOX エンジンの `/presets`、`/add_preset`、`/update_preset`、`/delete_preset`、`/audio_query_from_preset` に HTTP リクエストを送信します。

公式エンジンのプリセットは OS ごとのユーザー領域に `presets.yaml` として保存されます。VOICEVOXアプリ（製品版）macOS のビルド版では通常、次の場所に保存されます。

```text
~/Library/Application Support/voicevox-engine/presets.yaml
```

Docker で公式エンジンを起動している場合も、コンテナ内のユーザー領域に保存されます。コンテナを再起動しても同じコンテナを使い続けている間はデータが維持されます。ただし `docker run --rm` のように一時コンテナを都度作成している場合は、コンテナ終了時にプリセットが失われます。プリセットを確実に永続化したい場合は Docker volume でコンテナ内のユーザー領域をマウントしてください。

ネイティブモードの `storage/voicevox/presets.json` とは別管理です。Laravel 側の `config('voicevox.core.presets')` はクライアントモードには使われません。

## 事前準備

公式 VOICEVOX エンジンを起動します。

```shell
docker pull voicevox/voicevox_engine:cpu-latest
docker run --rm -p '127.0.0.1:50021:50021' voicevox/voicevox_engine:cpu-latest
```

接続先は `VOICEVOX_URL` で変更できます。

```env
VOICEVOX_URL=http://127.0.0.1:50021
```

## プリセットの構造

クライアントモードで送受信するプリセットは、公式エンジン API のスキーマに合わせた配列です。

```php
[
    'id' => 1,                // プリセット ID（整数）
    'name' => 'ゆっくり',      // プリセット名
    'speaker_uuid' => 'uuid', // スピーカー UUID（文字列）
    'style_id' => 1,          // スタイル ID（整数）
    'speedScale' => 0.8,      // 話速
    'pitchScale' => 0.0,      // 音高
    'intonationScale' => 1.0, // 抑揚
    'volumeScale' => 1.0,     // 音量
    'prePhonemeLength' => 0.1, // 開始無音
    'postPhonemeLength' => 0.1, // 終了無音
]
```

## 基本的な使い方

### プリセットの作成

`Voicevox::addPreset()` でプリセットを作成できます。公式エンジンでは `id` に `0` を指定すると未使用の ID が割り当てられます。

```php
use Revolution\Voicevox\Voicevox;

$id = Voicevox::addPreset([
    'id' => 0,
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

echo $id; // 例: 1
```

### 全プリセットの取得

```php
use Revolution\Voicevox\Voicevox;

$presets = Voicevox::presets();

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

### プリセットの更新

```php
use Revolution\Voicevox\Voicevox;

$id = Voicevox::updatePreset([
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

```php
use Revolution\Voicevox\Voicevox;

Voicevox::deletePreset(1);
```

## プリセットを使った音声合成

### talkFromPreset()

`Voicevox::talkFromPreset()` は公式エンジンの `/audio_query_from_preset` を使って、プリセット適用済みの `TalkAudioQuery` を作成します。

```php
use Revolution\Voicevox\Voicevox;

$response = Voicevox::talkFromPreset('プリセットを使うのだ', presetId: 1)
    ->generate(id: 1);

$response->storeAs('client', 'preset-talk.wav');
```

### audioQueryFromPreset()

音声合成前のオーディオクエリ配列だけを取得したい場合は `audioQueryFromPreset()` を使います。

```php
use Revolution\Voicevox\Voicevox;

$audioQuery = Voicevox::audioQueryFromPreset(
    text: 'プリセットのクエリだけ作るのだ',
    presetId: 1,
);

$audioQuery['speedScale'] = 1.1;
```

### tap() で追加調整

プリセットから作成した `TalkAudioQuery` も、通常の `Voicevox::talk()` と同じように `tap()` で調整できます。

```php
use Revolution\Voicevox\Client\TalkAudioQuery;
use Revolution\Voicevox\Voicevox;

$response = Voicevox::talkFromPreset('少し速く読むのだ', presetId: 1)
    ->tap(function (TalkAudioQuery $talk) {
        $talk->audioQuery['speedScale'] = 1.2;
    })
    ->generate(id: 1);
```

## Engine API との対応

`Voicevox` Facade の各メソッドは、公式エンジンの API に対応しています。

| Laravel | 公式エンジン API |
|---|---|
| `Voicevox::presets()` | `GET /presets` |
| `Voicevox::addPreset()` | `POST /add_preset` |
| `Voicevox::updatePreset()` | `POST /update_preset` |
| `Voicevox::deletePreset()` | `POST /delete_preset` |
| `Voicevox::audioQueryFromPreset()` | `POST /audio_query_from_preset` |
| `Voicevox::talkFromPreset()` | `POST /audio_query_from_preset` + `POST /synthesis` |

## ネイティブモードとの違い

| 項目 | クライアントモード | ネイティブモード |
|---|---|---|
| 利用方法 | `Voicevox` Facade | `preset()` / `talk()` ヘルパー |
| 保存形式 | 公式エンジンの `presets.yaml` | Laravel の `presets.json` |
| macOS の例 | `~/Library/Application Support/voicevox-engine/presets.yaml` | `storage/voicevox/presets.json` |
| 音声クエリ生成 | 公式エンジンの `/audio_query_from_preset` | Laravel 側のネイティブ実装 |
| エンジンプロセス | 必要 | 不要（FFI が必要） |

## 実用例

### ナレーション用

```php
use Revolution\Voicevox\Voicevox;

Voicevox::addPreset([
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

### 早口トーク用

```php
Voicevox::addPreset([
    'id' => 0,
    'name' => '早口',
    'speaker_uuid' => '388f246b-8c41-4ac1-8e2d-5d79f3ff56d9',
    'style_id' => 3,
    'speedScale' => 1.5,
    'pitchScale' => 0.05,
    'intonationScale' => 1.3,
    'volumeScale' => 1.0,
    'prePhonemeLength' => 0.05,
    'postPhonemeLength' => 0.05,
]);
```

## 注意事項

- クライアントモードでは公式エンジン側の `presets.yaml` が更新されます。Laravel プロジェクトの `storage` には保存されません。
- `docker run --rm` など一時コンテナを都度作成している場合、コンテナ終了時にプリセットが失われます。
- `speaker_uuid` と `style_id` の両方を含めてください。
- `talkFromPreset()` で作ったクエリを `generate(id:)` する場合、通常はプリセットの `style_id` と同じ ID を指定します。

## トラブルシューティング

### プリセットが取得できない

1. 公式 VOICEVOX エンジンが起動しているか確認してください。
2. `VOICEVOX_URL` が正しい接続先を指しているか確認してください。
3. Docker で別コンテナを起動し直していないか確認してください。

### プリセットファイルの場所がわからない

macOS のビルド版では次の場所を確認してください。

```text
~/Library/Application Support/voicevox-engine/presets.yaml
```

Docker では公式エンジンのユーザー領域（コンテナ内）に保存されます。永続化が必要な場合は、コンテナ内のユーザー領域を Docker volume にマウントしてください。

### speaker_uuid がわからない

スピーカー一覧から UUID を取得できます。

```php
use Revolution\Voicevox\Voicevox;

$speakers = Voicevox::speakers();

foreach ($speakers as $speaker) {
    echo $speaker['name'].': '.$speaker['speaker_uuid'].PHP_EOL;
}
```
