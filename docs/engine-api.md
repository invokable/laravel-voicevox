# VOICEVOXエンジンAPIの対応表

数種類のパターンがある。

- 先にLaravelで実行してエラーなら公式エンジンにフォールバック
- Laravelでは対応できないので最初からフォールバック
- コアもフォールバックも不要

| API                               | Laravel（PHP版コアを使用） | 公式エンジンにフォールバック | 説明                                                                                        |
|-----------------------------------|--------------------|----------------|-------------------------------------------------------------------------------------------|
| `GET /version`                    | ✅                  | ❌              | `"0.25.2"`のようなバージョンを返すだけなのでLaravelのみで可能                                                   |
| `GET /engine_manifest`            | ✅                  | ❌              | Laravelパッケージ用のマニフェストを静的に返す                                                                |
| `POST /validate_kana`             | ✅                  | ❌              | AquesTalk風記法カタカナのバリデーション                                                                  |
| `GET /_resources/{hash}`          | ✅                  | ❌              | キャラクター画像等のリソースファイルを返す                                                                     |
| `POST /audio_query`               | ⚠️                 | ✅              | Laravelではenable_katakana_englishには非対応                                                     |
| `POST /accent_phrases`            | ⚠️                 | ✅              | `is_kana`のtrue/false両対応。enable_katakana_englishには非対応。                                     |
| `POST /synthesis`                 | ✅                  | ✅              | コアで可能                                                                                     |
| `POST /mora_data`                 | ✅                  | ✅              | コアで可能                                                                                     |
| `POST /mora_length`               | ✅                  | ✅              | コアで可能                                                                                     |
| `POST /mora_pitch`                | ✅                  | ✅              | コアで可能                                                                                     |
| `GET /speakers`                   | ✅                  | ✅              | 公式のJSONと完全一致                                                                              |
| `GET /speaker_info`               | ✅                  | ✅              | 公式のJSONと完全一致                                                                              |
| `GET /singers`                    | ✅                  | ✅              | 公式のJSONと完全一致                                                                              |
| `GET /singer_info`                | ✅                  | ✅              | 公式のJSONと完全一致                                                                              |
| `POST /sing_frame_audio_query`    | ✅                  | ✅              | コアで可能                                                                                     |
| `POST /frame_synthesis`           | ✅                  | ✅              | コアで可能                                                                                     |
| `POST /sing_frame_f0`             | ⚠️                 | ✅              | `createSingFrameAudioQuery`でf0を近似。手動変更済みphonemeには非対応                                      |
| `POST /sing_frame_volume`         | ⚠️                 | ✅              | `createSingFrameAudioQuery`でvolumeを近似。変更後f0を入力できない制限あり。f0→volumeの順で更新する必要があるのでこの機能はほぼ非対応。 |
| `GET /user_dict`                  | ✅                  | ✅              | `NativeUserDict`でコアのFFI経由。コアが使えない場合はフォールバック。                                              |
| `POST /user_dict_word`            | ✅                  | ✅              | `NativeUserDict`でコアのFFI経由。コアが使えない場合はフォールバック。                                              |
| `PUT /user_dict_word/{word_uuid}` | ✅                  | ✅              | `NativeUserDict`でコアのFFI経由。コアが使えない場合はフォールバック。                                              |
| `DELETE /user_dict_word/{uuid}`   | ✅                  | ✅              | `NativeUserDict`でコアのFFI経由。コアが使えない場合はフォールバック。                                              |
| `POST /cancellable_synthesis`     | ❌                  | ✅              | コアに相当機能なし。フォールバックのみ。                                                                      |
| `POST /multi_synthesis`           | ❌                  | ✅              | コアに相当機能なし。フォールバックのみ。                                                                      |
| `POST /connect_waves`             | ❌                  | ✅              | コアに相当機能なし。フォールバックのみ。                                                                      |
| `POST /morphable_targets`         | ❌                  | ✅              | コアに相当機能なし。フォールバックのみ。                                                                      |
| `POST /synthesis_morphing`        | ❌                  | ✅              | コアに相当機能なし。フォールバックのみ。                                                                      |
| `GET /presets`                    | ✅                  | ✅              | `NativePresetStore`でJSONファイルに永続化。コアが使えない場合はフォールバック。                                       |
| `POST /add_preset`                | ✅                  | ✅              | `NativePresetStore`でJSONファイルに永続化。コアが使えない場合はフォールバック。                                       |
| `POST /update_preset`             | ✅                  | ✅              | `NativePresetStore`でJSONファイルに永続化。コアが使えない場合はフォールバック。                                       |
| `POST /delete_preset`             | ✅                  | ✅              | `NativePresetStore`でJSONファイルに永続化。コアが使えない場合はフォールバック。                                       |
| `POST /audio_query_from_preset`   | ✅                  | ✅              | `NativePresetStore`でプリセット取得後、コアの`createAudioQuery`でクエリ生成。プリセットが見つからない場合はフォールバック。          |
| `POST /import_user_dict`          | ❌                  | ✅              | コアのUserDictはバイナリ形式で入出力するためJSON直接インポート不可。フォールバックのみ。                                        |
| `POST /initialize_speaker`        | ❌                  | ✅              | コアはvvmロードで初期化済みのためAPIとして公開する必要がない。フォールバックのみ。                                              |
| `GET /is_initialized_speaker`     | ❌                  | ✅              | 同上                                                                                        |
| `GET /core_versions`              | ❌                  | ✅              | コアバージョン一覧はエンジン固有の機能。フォールバックのみ。                                                            |
| `GET /supported_devices`          | ❌                  | ✅              | デバイス情報はエンジン固有の機能。フォールバックのみ。                                                               |
| `GET /downloadable_libraries`     | ❌                  | ✅              | ライブラリ管理はエンジン固有の機能。フォールバックのみ。                                                              |
| `GET /installed_libraries`        | ❌                  | ✅              | 同上                                                                                        |
| `POST /install_library/{uuid}`    | ❌                  | ✅              | 同上                                                                                        |
| `POST /uninstall_library/{uuid}`  | ❌                  | ✅              | 同上                                                                                        |
| `GET /setting`                    | ❌                  | ✅              | 設定画面はエンジン固有の機能。フォールバックのみ。                                                                 |
| `POST /setting`                   | ❌                  | ✅              | 同上                                                                                        |
| `GET /`                           | ❌                  | ✅              | ウェルカムページはエンジン固有の機能。フォールバックのみ。                                                             |
