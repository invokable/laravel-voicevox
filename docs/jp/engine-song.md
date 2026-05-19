# エンジン API モード：ソング（歌声音声合成）

Laravel 版エンジン API は、公式 VOICEVOX エンジンと互換性のある歌声合成エンドポイントも提供します。

## 事前準備

エンジン API の基本的なセットアップは [engine-talk.md](engine-talk.md) の手順と同じです。  
歌声モデルを使うには `config/voicevox.php` の `core.vvms` に歌声用 `s0.vvm` が含まれていることを確認してください（デフォルトで含まれています）。歌声用モデルは `s0.vvm` 一つだけなので全てのスタイルIDを使用できます。

```dotenv
VOICEVOX_CORE_PATH=/path/to/voicevox_core/
```

## Laravel 版エンジンの起動

```shell
php artisan serve
```

## 歌声音声の生成

### 1. sing_frame_audio_query の作成

```shell
curl -s -X POST "http://127.0.0.1:8000/sing_frame_audio_query?speaker=6000" \
  -H "Content-Type: application/json" \
  -d '{
    "notes": [
      {"id": "a", "key": null, "frame_length": 15, "lyric": ""},
      {"id": "b", "key": 60, "frame_length": 94, "lyric": "ド"},
      {"id": "c", "key": 62, "frame_length": 94, "lyric": "レ"},
      {"id": "d", "key": 64, "frame_length": 187, "lyric": "ミ"},
      {"id": "e", "key": null, "frame_length": 2, "lyric": ""}
    ]
  }' \
  > frame_audio_query.json
```

`speaker` には `sing` または `singing_teacher` 種別のスタイル ID（例: 6000）を指定します。

### 2. frame_synthesis で音声合成

```shell
curl -s -X POST "http://127.0.0.1:8000/frame_synthesis?speaker=3001" \
  -H "Content-Type: application/json" \
  -d @frame_audio_query.json \
  > song.wav
```

`speaker` には `sing` または `frame_decode` 種別のスタイル ID（例: 3001）を指定します。

## Laravel クライアントから使う

Laravel 版エンジンに向けてクライアントモードの `Voicevox::song()` を使うこともできます。

```php
// config/voicevox.php
'client' => [
    'url' => env('VOICEVOX_URL', 'http://127.0.0.1:8000'), // Laravel エンジンの URL
],
```

```php
use Revolution\Voicevox\Song\Note;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\Voicevox;

$score = Score::make([
    Note::make(length: 15),
    Note::make(length: Note::len(480, 120), lyric: 'ド', key: 60),
    Note::make(length: Note::len(480, 120), lyric: 'レ', key: 62),
    Note::make(length: Note::len(960, 120), lyric: 'ミ', key: 64),
    Note::make(length: 2),
]);

$response = Voicevox::song($score, teacher: 6000)->generate(id: 3001);

$response->storeAs('engine', 'song.wav');
```

## F0・ボリューム調整エンドポイント

フレームオーディオクエリーを作成した後、F0（基本周波数）やボリュームを個別に調整するエンドポイントも提供しています。

```shell
# F0 の更新（sing_frame_audio_query の結果 JSON が必要）
curl -s -X POST "http://127.0.0.1:8000/sing_frame_f0?speaker=6000" \
  -H "Content-Type: application/json" \
  -d '{"score": {...}, "frame_audio_query": {...}}' \
  > f0.json

# ボリュームの更新（必ず F0 更新の後に実行）
curl -s -X POST "http://127.0.0.1:8000/sing_frame_volume?speaker=6000" \
  -H "Content-Type: application/json" \
  -d '{"score": {...}, "frame_audio_query": {...}}' \
  > volume.json
```

F0 → ボリュームの順で更新するのは VOICEVOX CORE の仕様です。

## ソング関連の対応状況

| エンドポイント | Laravel 版 | フォールバック | 備考 |
|---|---|---|---|
| `POST /sing_frame_audio_query` | ✅ | ✅ | |
| `POST /frame_synthesis` | ✅ | ✅ | |
| `POST /sing_frame_f0` | ✅ | ✅ | |
| `POST /sing_frame_volume` | ✅ | ✅ | f0 → volume の順で更新 |
| `GET /singers` | ✅ | ✅ | |
| `GET /singer_info` | ✅ | ✅ | リソースインストールが必要 |

詳細な対応表は [engine-api.md](../engine-api.md) を参照してください。
