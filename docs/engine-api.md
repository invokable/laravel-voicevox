# VOICEVOXエンジンAPIの対応表

数種類のパターンがある。

- 先にLaravelで実行してエラーなら公式エンジンにフォールバック
- Laravelでは対応できないので最初からフォールバック
- コアもフォールバックも不要

| API                       | Laravel（PHP版コアを使用） | 公式エンジンにフォールバック | 説明                                                                                        |
|---------------------------|--------------------|----------------|-------------------------------------------------------------------------------------------|
| `/version`                | ✅                  | ❌              | `"0.25.2"`のようなバージョンを返すだけなのでLaravelのみで可能                                                   |
| `/audio_query`            | ⚠️                 | ✅              | Laravelではenable_katakana_englishには非対応                                                     |
| `/accent_phrases`         | ⚠️                 | ✅              | `is_kana`のtrue/false両対応。enable_katakana_englishには非対応。                                     |
| `/synthesis`              | ✅                  | ✅              | コアで可能                                                                                     |
| `/mora_data`              | ✅                  | ✅              | コアで可能                                                                                     |
| `/mora_length`            | ✅                  | ✅              | コアで可能                                                                                     |
| `/mora_pitch`             | ✅                  | ✅              | コアで可能                                                                                     |
| `/speakers`               | ✅                  | ✅              | 公式のJSONと完全一致                                                                              |
| `/singers`                | ✅                  | ✅              | 公式のJSONと完全一致                                                                              |
| `/speaker_info`           | ✅                  | ✅              | 公式のJSONと完全一致                                                                              |
| `/singer_info`            | ✅                  | ✅              | 公式のJSONと完全一致                                                                              |
| `/validate_kana`          | ✅                  | ❌              |                                                                                           |
| `/engine_manifest`        | ✅                  | ❌              |                                                                                           |
| `/sing_frame_audio_query` | ✅                  | ✅              | コアで可能                                                                                     |
| `/frame_synthesis`        | ✅                  | ✅              | コアで可能                                                                                     |
| `/sing_frame_f0`          | ⚠️                 | ✅              | `createSingFrameAudioQuery`でf0を近似。手動変更済みphonemeには非対応                                      |
| `/sing_frame_volume`      | ⚠️                 | ✅              | `createSingFrameAudioQuery`でvolumeを近似。変更後f0を入力できない制限あり。f0→volumeの順で更新する必要があるのでこの機能はほぼ非対応。 |
