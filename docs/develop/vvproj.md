# vvprojファイル仕様メモ

`.vvproj` はVOICEVOXエディターのプロジェクトファイルで、中身はUTF-8 JSON。トークとソングを同じファイルに保存できる。

この資料は、Laravel版パッケージなどで `.vvproj` を読み込み、トーク音声やソング音声を生成したり、JSONを直接編集したりするための実用メモ

## トップレベル構造

```jsonc
{
  "appVersion": "0.25.2",
  "talk": {
    "audioKeys": [],
    "audioItems": {}
  },
  "song": {
    "tpqn": 480,
    "tempos": [],
    "timeSignatures": [],
    "tracks": {},
    "trackOrder": []
  }
}
```

| キー | 内容 |
|---|---|
| `appVersion` | 保存したVOICEVOXエディターのバージョン。本アプリではv0.17.0以降を対象にしている。 |
| `talk` | トーク用の音声アイテム。`audioKeys` が順序、`audioItems` がIDをキーにした本体。 |
| `song` | ソング用の楽曲データ。TPQN、テンポ、拍子、トラック、表示順を持つ。 |

## talk

`talk.audioKeys` はトークアイテムIDの配列で、表示・再生順を表す。`talk.audioItems` は同じIDをキーにしたRecord。

```jsonc
{
  "talk": {
    "audioKeys": ["audio-item-uuid"],
    "audioItems": {
      "audio-item-uuid": {
        "text": "ずんだもんなのだ",
        "voice": {
          "engineId": "engine-uuid",
          "speakerId": "speaker-uuid",
          "styleId": 3
        },
        "query": {
          "accentPhrases": [],
          "speedScale": 1,
          "pitchScale": 0,
          "intonationScale": 1,
          "volumeScale": 1,
          "pauseLengthScale": 1,
          "prePhonemeLength": 0.1,
          "postPhonemeLength": 0.1,
          "outputSamplingRate": 24000,
          "outputStereo": false,
          "kana": ""
        },
        "presetKey": "preset-uuid"
      }
    }
  }
}
```

### TalkAudioItem

| キー | 内容 |
|---|---|
| `text` | 入力テキスト。 |
| `voice.engineId` | 使用エンジンのID。`/engine_manifest` のUUIDと対応する。 |
| `voice.speakerId` | 話者ID。VOICEVOXのspeaker UUID。 |
| `voice.styleId` | トーク用style ID。APIの`speaker`パラメータとして使う。 |
| `query` | VOICEVOX Engineの `AudioQuery` 相当。 |
| `presetKey` | エディター上のプリセットID。直接生成では必須ではない。 |

`query` は `/audio_query` の結果とほぼ同じ形なので、音声生成時は `query.accentPhrases` と各スケール値を使えばよい。`.vvproj` に保存済みのクエリを使う場合、再度 `/audio_query` を呼ばずに `/synthesis?speaker={styleId}` へ `query` を渡せる。

### accentPhrases

```jsonc
{
  "accentPhrases": [
    {
      "moras": [
        {
          "text": "ズ",
          "consonant": "z",
          "consonantLength": 0.127,
          "vowel": "u",
          "vowelLength": 0.113,
          "pitch": 5.77
        },
        {
          "text": "ン",
          "vowel": "N",
          "vowelLength": 0.093,
          "pitch": 6.10
        }
      ],
      "accent": 1,
      "isInterrogative": false
    }
  ]
}
```

| キー | 内容 |
|---|---|
| `moras` | モーラ配列。`consonant` と `consonantLength` は母音のみ・撥音などでは省略される。 |
| `accent` | アクセント位置。1始まり。 |
| `pauseMora` | 無音がある場合に入ることがある。 |
| `isInterrogative` | 疑問文フラグ。 |

Laravel版パッケージでトークを生成する場合は、`audioKeys` 順に `audioItems` を取り出し、`voice.styleId` と `query.accentPhrases` を中心に `AudioQuery` を復元して合成する。

## song

```jsonc
{
  "song": {
    "tpqn": 480,
    "tempos": [{ "position": 0, "bpm": 120 }],
    "timeSignatures": [{ "measureNumber": 1, "beats": 4, "beatType": 4 }],
    "tracks": {
      "track-uuid": {
        "name": "無名トラック",
        "singer": {
          "engineId": "engine-uuid",
          "styleId": 3003
        },
        "keyRangeAdjustment": 0,
        "volumeRangeAdjustment": 0,
        "notes": [],
        "pitchEditData": [],
        "volumeEditData": [],
        "phonemeTimingEditData": {},
        "solo": false,
        "mute": false,
        "gain": 1,
        "pan": 0
      }
    },
    "trackOrder": ["track-uuid"]
  }
}
```

| キー | 内容 |
|---|---|
| `tpqn` | Ticks Per Quarter Note。公式デフォルトは480で、MIDIのTPQN 480と同じ考え方。 |
| `tempos` | テンポマップ。`position` はtick、`bpm` はその位置以降のBPM。 |
| `timeSignatures` | 拍子マップ。`measureNumber` は1始まり。 |
| `tracks` | Track IDをキーにしたRecord。 |
| `trackOrder` | 表示・再生時のトラック順。`tracks` のキーと過不足なく一致させる。 |

### Track

| キー | 内容 |
|---|---|
| `name` | トラック名。 |
| `singer.engineId` | 歌唱エンジンID。 |
| `singer.styleId` | ソング用style ID。最終的な `/frame_synthesis` の`speaker`に使う。 |
| `keyRangeAdjustment` | 音域調整。半音単位でピッチに反映する。 |
| `volumeRangeAdjustment` | 声量調整。dB相当として音量に反映する。 |
| `notes` | ノート配列。 |
| `pitchEditData` | フレーム単位のピッチ編集値。未編集なら空配列。 |
| `volumeEditData` | フレーム単位の音量編集値。未編集なら空配列。 |
| `phonemeTimingEditData` | Note IDをキーにした音素タイミング編集Record。未編集なら空オブジェクト。 |
| `solo` / `mute` | ミキサー状態。soloトラックがあればsoloのみ、なければmuteでないトラックを再生対象にする。 |
| `gain` | トラック音量。デフォルト1。 |
| `pan` | パン。おおむね -1.0 から 1.0。 |

### Note

```jsonc
{
  "id": "note-uuid",
  "position": 2400,
  "duration": 480,
  "noteNumber": 62,
  "lyric": "レ"
}
```

| キー | 内容 |
|---|---|
| `id` | ノートID。音素タイミング編集などから参照されるので一意にする。 |
| `position` | ノート開始位置。tick単位。 |
| `duration` | ノート長。tick単位。TPQN 480なら四分音符は480tick。 |
| `noteNumber` | MIDIノート番号。中央Cは60。 |
| `lyric` | 歌詞。省略時は生成側で「ら」などのデフォルト歌詞を補う。 |

## tick・秒・フレームの変換

ソング生成では `.vvproj` の `position` / `duration` を、エンジンAPIの `ScoreNote.frame_length` に変換する。基本はTPQN 480を基準にしたMIDI風のtick計算。

単一テンポの場合:

```text
seconds = ticks / tpqn * 60 / bpm
ticks = seconds * bpm / 60 * tpqn
frame = round(seconds * frameRate)
frame_length = round(noteOffSeconds * frameRate) - round(noteOnSeconds * frameRate)
```

テンポ変更がある場合は、`tempos` を `position` 昇順で走査し、テンポ区間ごとに秒数を積算する。

拍子は小節表示やスナップ用。音声フレーム長には直接使わない。

```text
beatTicks = tpqn * 4 / beatType
measureTicks = beatTicks * beats
```

Laravel版パッケージ側では、MIDIのTPQN 480をベースにしたフレーム長計算ヘルパーを使えば、`.vvproj` の `notes` から歌唱用 `Score` を生成できる。

## ソング音声生成の流れ

1. `trackOrder` 順に `tracks` を取り出す。
2. solo/muteを見て生成対象トラックを決める。
3. `notes` を `position` 昇順に並べ、連続ノートをフレーズ化する。
4. 各フレーズの先頭に休符、末尾に休符を足して `Score` を作る。休符は `key: null`, `lyric: ""`。
5. `ScoreNote` は `id`, `key`, `frame_length`, `lyric` を持つ。`key` はMIDIノート番号、`frame_length` は上記のtick→秒→フレーム変換で作る。
6. `/sing_frame_audio_query?speaker=6000`、`/sing_frame_f0?speaker=6000`、`/sing_frame_volume?speaker=6000`、`/frame_synthesis?speaker={singer.styleId}` の順に呼ぶ。
7. 必要に応じて `keyRangeAdjustment`, `volumeRangeAdjustment`, `pitchEditData`, `volumeEditData`, `phonemeTimingEditData` を反映する。

`6000` は歌唱教師スタイルID。公式エンジンとLaravel版エンジンで同じ値として扱える。

## 直接編集時の注意

- `tracks` のキーと `trackOrder` は完全一致させる。欠落・重複・余分なIDがあると読み込みエラーにするのが安全。
- `talk.audioKeys` と `talk.audioItems` も同様に、順序配列とRecord本体を同期する。
- `position` は0以上、`duration` は1以上、`noteNumber` は0から127。
- `tempos[0].position` は通常0。BPMは40以上を想定する。
- `timeSignatures[0].measureNumber` は通常1。
- IDはUUID文字列にしておくと公式ファイルと互換性を保ちやすい。
- `engineId` は登録済みエンジンの `/engine_manifest` と一致させる。公式エンジンとLaravel版エンジンを併用する場合は、IDからURLを引けるようにする。
- 未対応または将来追加されたキーは、可能なら破棄せず保持する。
