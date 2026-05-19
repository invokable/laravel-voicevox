# クライアントモード：ソング（歌声音声合成）

クライアントモードの歌声合成では、公式 VOICEVOX エンジンの `sing_frame_audio_query` と `frame_synthesis` API を使います。`Score`（楽譜）と `Note`（音符）を組み立ててから合成します。

## 事前準備

Docker で公式 VOICEVOX エンジンを起動します。

```shell
docker pull voicevox/voicevox_engine:cpu-latest
docker run --rm -p '127.0.0.1:50021:50021' voicevox/voicevox_engine:cpu-latest
```

## Score と Note の作成

```php
use Revolution\Voicevox\Song\Note;
use Revolution\Voicevox\Song\Score;

$score = Score::make([
    Note::make(length: 15),                                      // 1音目は必ず休符
    Note::make(length: Note::len(ticks: 480, bpm: 120), lyric: 'ド', key: 60), // 1拍
    Note::make(length: Note::len(480, 120), lyric: 'レ', key: 62),              // 1拍
    Note::make(length: Note::len(960, 120), lyric: 'ミ', key: 64),              // 2拍
    Note::make(length: 2),                                       // 最後も短い休符を入れるとよい
]);
```

### Note のパラメーター

| パラメーター | 型 | 説明 |
|---|---|---|
| `length` | int | フレーム長（93.75 フレーム = 1 秒相当） |
| `lyric` | string | 歌詞（空文字または省略で休符） |
| `key` | int\|null | MIDI ノート番号（60 = ド、省略で休符） |

### Note::len() ヘルパー

MIDI に慣れた方向け。四分音符を 480 ticks として BPM からフレーム長を計算します。

```php
// 120 BPM で四分音符 1 拍
$length = Note::len(ticks: 480, bpm: 120); // ≒ 94 フレーム
```

## 基本的な使い方

`Voicevox::song()` でフレームオーディオクエリーを生成し、`generate()` で音声を合成します。

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

// teacher: 歌声モデルのスタイル ID（6000 = ずんだもん歌声）
// generate(id:) : frame_synthesis で使うスタイル ID（3001 = ずんだもん あまあま）
$response = Voicevox::song($score, teacher: 6000)->generate(id: 3001);

$response->storeAs('client', 'song.wav');
```

### teacher と id の使い分け

- **teacher**: `sing_frame_audio_query` で使うスタイル ID。種類が `sing` または `singing_teacher` のもの（例: 6000）。
- **id（generate 引数）**: `frame_synthesis` で使うスタイル ID。種類が `sing` または `frame_decode` のもの（例: 3001）。

利用可能な歌声スピーカーの一覧は `singers()` で取得できます。

```php
$singers = Voicevox::singers();
```

## tap() で F0・ボリュームを調整

`tap()` を使ってフレームオーディオクエリーのパラメーターを調整できます。F0（基本周波数）とボリュームを手動で再計算する場合は **必ず F0 → ボリュームの順** で更新します。

```php
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\Client\SongAudioQuery;

$response = Voicevox::song($score, teacher: 6000)
    ->tap(function (SongAudioQuery $song) {
        // F0 を更新
        $song->updateF0();

        // ボリュームを更新（必ず F0 の後）
        $song->updateVolume();

        // または F0 + ボリュームをまとめて更新
        $song->sync();
    })
    ->generate(id: 3001);

$response->storeAs('client', 'song.wav');
```

Score の Note を変更した後に F0 とボリュームを再同期したい場合は `sync()` が便利です。

```php
->tap(function (SongAudioQuery $song) {
    // Score の音程を変更
    $song->score->notes[2] = Note::make(length: Note::len(480, 120), lyric: 'ファ', key: 65);

    // 変更を F0・ボリュームに反映
    $song->sync();
})
```

## メソッドチェーン全体の流れ

```
Voicevox::song($score, teacher: $teacherId)
    → SongAudioQuery（sing_frame_audio_query の結果を保持）
        → tap() でパラメーター調整（任意）
        → generate(id: $speakerId)
            → VoicevoxResponse（WAV 音声データ）
                → storeAs() / content()
```
