# VOICEVOXエンジンAPIの対応表

数種類のパターンがある。

- 先にLaravelで実行してエラーなら公式エンジンにフォールバック
- Laravelでは対応できないので最初からフォールバック
- フォールバックはしてるけど同じようには動作しないパターン
- コアもフォールバックも不要

| API                               | Laravel（PHP版コアを使用） | 公式エンジンにフォールバック | 説明                                                                            |
|-----------------------------------|--------------------|----------------|-------------------------------------------------------------------------------|
| `GET /version`                    | ✅                  | ❌              | `"0.25.2"`のようなバージョンを返すだけなのでLaravelのみで可能                                       |
| `GET /engine_manifest`            | ✅                  | ❌              | Laravelパッケージ用のマニフェストを静的に返す                                                    |
| `POST /validate_kana`             | ✅                  | ❌              | AquesTalk風記法カタカナのバリデーション                                                      |
| `GET /_resources/{hash}`          | ✅                  | ❌              | キャラクター画像等のリソースファイルを返す                                                         |
| `POST /audio_query`               | ⚠️                 | ✅              | Laravelではenable_katakana_englishには非対応                                         |
| `POST /accent_phrases`            | ⚠️                 | ✅              | `is_kana`のtrue/false両対応。enable_katakana_englishには非対応。                         |
| `POST /synthesis`                 | ✅                  | ✅              | コアで可能                                                                         |
| `POST /v1/audio/speech`           | ✅                  | ✅              | OpenAI互換TTS。`voice`はスタイルID・AI SDKエイリアス・OpenAIボイス名に対応。`speed`は`speedScale`へ適用。 |
| `POST /mora_data`                 | ✅                  | ✅              | コアで可能                                                                         |
| `POST /mora_length`               | ✅                  | ✅              | コアで可能                                                                         |
| `POST /mora_pitch`                | ✅                  | ✅              | コアで可能                                                                         |
| `GET /speakers`                   | ✅                  | ✅              | 公式のJSONと完全一致                                                                  |
| `GET /speaker_info`               | ✅                  | ✅              | 公式のJSONと完全一致                                                                  |
| `GET /singers`                    | ✅                  | ✅              | 公式のJSONと完全一致                                                                  |
| `GET /singer_info`                | ✅                  | ✅              | 公式のJSONと完全一致                                                                  |
| `POST /sing_frame_audio_query`    | ✅                  | ✅              | コアで可能                                                                         |
| `POST /frame_synthesis`           | ✅                  | ✅              | コアで可能                                                                         |
| `POST /sing_frame_f0`             | ✅                  | ✅              | `createSingFrameF0`でコアが直接対応                                                   |
| `POST /sing_frame_volume`         | ✅                  | ✅              | `createSingFrameVolume`でコアが直接対応。f0→volumeの順で更新する必要があるのはコアの仕様。                 |
| `GET /user_dict`                  | ✅                  | ❌              | `NativeUserDict`でコアのFFI経由。公式エンジンとデータを共有しておらずフォールバックは無意味なのでLaravelのみ。          |
| `POST /user_dict_word`            | ✅                  | ❌              | 同上                                                                            |
| `PUT /user_dict_word/{word_uuid}` | ✅                  | ❌              | 同上                                                                            |
| `DELETE /user_dict_word/{uuid}`   | ✅                  | ❌              | 同上                                                                            |
| `POST /import_user_dict`          | ✅                  | ❌              | 普通のjsonなので対応可能。フォールバックなし。                                                     |
| `POST /cancellable_synthesis`     | ❌                  | ✅              | コアに相当機能なし。フォールバックのみ。公式でもデフォルト無効なので対応不要。                                       |
| `POST /multi_synthesis`           | ❌                  | ✅              | コアに相当機能なし。フォールバックのみ。公式エンジンではzipで複数ファイルをレスポンス。synthesisを複数回実装してzip化なら可能かもしれない。 |
| `POST /connect_waves`             | ❌                  | ✅              | コアに相当機能なし。フォールバックのみ。                                                          |
| `POST /morphable_targets`         | ❌                  | ✅              | コアに相当機能なし。フォールバックのみ。supported_featuresを見れば対応は可能だけどこれだけ対応しても意味がないので非対応。        |
| `POST /synthesis_morphing`        | ❌                  | ✅              | コアに相当機能なし。フォールバックのみ。                                                          |
| `GET /presets`                    | ✅                  | ❌              | `NativePresetStore`でJSONファイルに永続化。公式エンジンとデータを共有しておらずフォールバックは無意味なのでLaravelのみ。   |
| `POST /add_preset`                | ✅                  | ❌              | 同上                                                                            |
| `POST /update_preset`             | ✅                  | ❌              | 同上                                                                            |
| `POST /delete_preset`             | ✅                  | ❌              | 同上                                                                            |
| `POST /audio_query_from_preset`   | ✅                  | ❌              | `NativePresetStore`でプリセット取得後、コアの`createAudioQuery`でクエリ生成。フォールバックなし。           |
| `POST /initialize_speaker`        | ❌                  | ✅              | コアはvvmロードで初期化済みのためAPIとして公開する必要がない。フォールバックのみ。                                  |
| `GET /is_initialized_speaker`     | ❌                  | ✅              | 同上                                                                            |
| `GET /core_versions`              | ✅                  | ❌              | コアの`VoicevoxCore::getVersion()`で対応可能。フォールバックなし。                               |
| `GET /supported_devices`          | ❌                  | ✅              | デバイス情報はエンジン固有の機能。フォールバックのみ。                                                   |
| `GET /downloadable_libraries`     | ❌                  | ✅              | ライブラリ管理はエンジン固有の機能。フォールバックのみ。対応不要。                                             |
| `GET /installed_libraries`        | ❌                  | ✅              | 同上                                                                            |
| `POST /install_library/{uuid}`    | ❌                  | ✅              | 同上                                                                            |
| `POST /uninstall_library/{uuid}`  | ❌                  | ✅              | 同上                                                                            |
| `GET /setting`                    | ❌                  | ✅              | 設定画面はエンジン固有の機能。フォールバックのみ。CORS設定は関係ないけどユーザー辞書のインポート・エクスポートが設定画面にある。            |
| `POST /setting`                   | ❌                  | ✅              | 同上                                                                            |
| `GET /`                           | ❌                  | ✅              | ウェルカムページはエンジン固有の機能。フォールバックのみ。                                                 |
