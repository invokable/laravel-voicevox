# ネイティブモード：ソング（歌声音声合成）

ネイティブモードの歌声合成では、PHP FFI で VOICEVOX CORE を直接呼び出します。`Score` と `Note` の組み立て方はクライアントモードと共通です。

## 事前準備

1. `voicevox-core-php` をインストール済みであること
2. VOICEVOX CORE 動的ライブラリをセットアップ済みであること（[インストール手順](installation.md) を参照）
3. `.env` でコアのパスを設定済みであること

```dotenv
VOICEVOX_CORE_PATH=/path/to/voicevox_core/
```

歌声モデルを使うには、`config/voicevox.php` の `core.vvms` に歌声用 `s0.vvm` が含まれていることを確認してください（デフォルトで含まれています）。

## Score と Note の作成

`Score` と `Note` はクライアントモードと共通クラスです。

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

`Note::len($ticks, $bpm)` は MIDI の ticks と BPM からフレーム長を計算するヘルパーです。

## 基本的な使い方

`song()` ヘルパー関数を使います。

```php
use Revolution\Voicevox\Song\Note;
use Revolution\Voicevox\Song\Score;
use function Revolution\Voicevox\song;

$score = Score::make([
    Note::make(length: 15),
    Note::make(length: Note::len(480, 120), lyric: 'ド', key: 60),
    Note::make(length: Note::len(480, 120), lyric: 'レ', key: 62),
    Note::make(length: Note::len(960, 120), lyric: 'ミ', key: 64),
    Note::make(length: 2),
]);

// teacher: 歌声モデルのスタイル ID（6000 = ずんだもん歌声）
// generate(id:) : frame_synthesis で使うスタイル ID（3001 = ずんだもん あまあま）
$response = song($score, teacher: 6000)->generate(id: 3001);

$response->storeAs('native', 'song.wav');
```

同じ `Score` を使えばクライアントモードとネイティブモードで同じ音声ファイルが生成されます。

## tap() で F0・ボリュームを調整

`tap()` でフレームオーディオクエリーのパラメーターを調整できます。

```php
use Revolution\Voicevox\Song\SongAudioQuery;
use function Revolution\Voicevox\song;

$response = song($score, teacher: 6000)
    ->tap(function (SongAudioQuery $song) {
        // F0 とボリュームをまとめて再計算（推奨）
        $song->sync();
    })
    ->generate(id: 3001);
```

Score の Note を変更した後に F0 とボリュームを個別に更新することもできます。**必ず F0 → ボリュームの順で更新**してください。

```php
->tap(function (SongAudioQuery $song) {
    // F0 を更新
    $song->updateF0();

    // ボリュームを更新（必ず F0 の後）
    $song->updateVolume();
})
```

## クライアントモードとの違い

| 項目 | クライアントモード | ネイティブモード |
|---|---|---|
| エンジン起動 | 必要（Docker 等） | 不要 |
| FFI | 不要 | 必要 |
| 使用する関数 | `Voicevox::song()` | `song()` |
| Score / Note | 共通 | 共通 |

## メソッドチェーン全体の流れ

```
song($score, teacher: $teacherId)
    → SongAudioQuery（sing_frame_audio_query の結果を保持）
        → tap() でパラメーター調整（任意）
        → generate(id: $speakerId)
            → VoicevoxResponse（WAV 音声データ）
                → storeAs() / content()
```
