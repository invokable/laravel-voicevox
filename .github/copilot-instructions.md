# VOICEVOX for Laravel Project Guidelines

## Overview

[VOICEVOX](https://github.com/VOICEVOX) for Laravel.

全てローカルで動かす前提。Webサーバー上で動かすのは難しいので優先度は低い。

GitHub Agentic Workflowsを使って少しずつ実装を進める。

## VOICEVOXの構成

- [VOICEVOXエディター](https://github.com/VOICEVOX/voicevox)：GUIアプリケーション。このプロジェクトでは関係ない。Electron、TypeScript、Vue。
- [VOICEVOXエンジン](https://github.com/VOICEVOX/voicevox_engine)：Webサーバーとして提供されるテキスト音声合成 API。Python、FastAPI、OpenJTalk。
- [VOICEVOXコア](https://github.com/VOICEVOX/voicevox_core)：音声合成の動的ライブラリ。Rust、onnxruntime。C APIの動的ライブラリ（.so/.dll/.dylib）がある。

VOICEVOXエンジンのAPIを呼び出すサードパーティ製のクライアントパッケージ。
https://github.com/voicevox-client

### OpenAPI

- .github/openapi.json

## Technology Stack

- **Language**: PHP 8.3+
- **Framework**: Laravel 12.x+
- **Testing**: Pest PHP 4.x
- **Code Quality**: Laravel Pint (PSR-12)
- [voicevox-core-php](https://github.com/invokable/voicevox-core-php)

## Command

- `composer run test` - Run pest tests.
- `composer run lint` - Run pint code formatter.

Agentic Workflows環境でも`setup-php`でPHPはインストールされてるはずだけど動かなくても後で通常のGitHub Actionsでtestとlintが実行される。

動的ライブラリ込みのテストはGitHub Actions専用に`test:integration`と`.github/workflows/integration-tests.yml`を用意する。voicevox-core-phpと同じ。

開発環境での手動動作テスト用コマンドは`workbench/routes/console.php`に実装。

## コーディングガイドライン

- クライアント：VOICEVOXエンジンのAPIと一対一の対応ではなくLaravelスタイルのクラス名やメソッド名を使う。
- VOICEVOXは歴史的経緯によりSpeakerId(UUID) と StyleId(整数)が混同している。後発のLaravel版では気にしなくていいので将来的に変更されてもいいように引数では`int|string $id`とする。
- VOICEVOXエンジンやコア内部の命名は、テキスト音声合成は`talk`や`tts`、歌声音声合成は`song`が使われている模様。公開APIは変更しにくいけど内部は変更できるので後からでも変わっている。最新の公式に合わせてLaravel版でもTalkとSongを使う。

## ディレクトリ構成

- src/Voicevox.php: クライアント機能のFacade。ユーザーが使う。
- src/Synthesizer.php: コア機能のFacade。ユーザーにはなるべく見せない設計。コアのSynthesizerはVoicevoxServiceProviderでコンテナに登録しているのでSynthesizer FacadeからコアSynthesizerの機能を全て使えてテスト時のモックもしやすい。`getMockableClass()`がないとモックできなかったので追加。ネイティブ版やエンジンはSynthesizer Facadeを使えば作りやすいはず。
- src/Client/: クライアント機能ディレクトリ
- src/Talk/: ネイティブのトーク機能ディレクトリ。VOICEVOX公式ではトークとソングを2大機能のように扱っているのでsrc直下に配置。
- src/Song/: ネイティブのソング機能ディレクトリ
- src/functions.php: `talk()`や`song()`のネイティブ版ヘルパー関数。クライアントはVoicevox Facadeから使う、ネイティブ版はヘルパーから使う全く別の導線。
- src/Engine/: エンジン機能ディレクトリ。これから開発。Talk、Song以外のコアを使う機能（辞書など）はEngine内に配置するかも。基本的に全てヘルパーからの利用を想定しているのでクラスファイルの配置場所は分かりやすければどこでもいい。コアではなくエンジンで実装してる機能も意外とあるので独自開発が必要かも。
- src/Support/: クライアントでもエンジンでも全部で使いそうなヘルパー。
- src/Console/: ユーザーが使うartisanコマンド。エンジンで使うresourcesのインストールなど。
- src/VoicevoxResponse.php: 音声の生データを保持するレスポンス。ひとまず全部で共通のVoicevoxResponseを使用。分けた方が良くなったら別クラス化。
- src/VoicevoxServiceProvider.php
- config/voicevox.php
- src/Ai/: [Laravel AI SDK](https://github.com/laravel/ai) 連携。AI SDKのAudioを使った音声合成を実装。`Audio::of('I love coding with Laravel.')->voice('ずんだもん')->generate();`。`voicevox-client`がクライアント版。`voicevox`がネイティブ版。
- voicevox_engine: 公式エンジンのgitサブモジュール
- voicevox_resource: resourcesのサブモジュール。公式エンジンでは[process_voicevox_resource.bash](../voicevox_engine/tools/process_voicevox_resource.bash) などで使用している。必要なファイルだけLaravel側にコピー。`/speaker_info'`や`/singer_info`で必要。
- resources: voicevox_resourceもしくはGitHubからダウンロードしたキャラ情報。500MB以上のサイズなので別途インストール。

## ドキュメント

クライアントもネイティブもエンジンも機能が揃ってきたのでドキュメントを整備。
README.mdは英語、README_jp.mdは日本語。
docs/jp内に日本語のドキュメントをmarkdownで作成。

ドキュメントサイトは別であるので英語版ドキュメントを含む詳細はそちらに掲載する。READMEとdocsを参考に日本語・英語で作る。AIが翻訳するのでdocsは日本語のみでいい。

- docs/develop/内は開発用の資料。
- docs/engine-api.md エンジンAPIの対応表

## 管理者からの指示

Agentic Workflows向けの次に行う作業の方向性をここで指示します。`[ ]`で未完了のタスクを優先して行なってください。未完了タスクがなければここの指示は無視します。

- [x] クライアント、ネイティブ、エンジンAPIまで主要な機能は実装できてきた。エンジンAPIはフォールバックのみでネイティブ対応できてない機能が少し残っているけどなくても困らないのでほぼ完了。
- [ ] `tests/Integration/`内の動的ライブラリ込みの統合テストを増やす。Agentic Workflows環境では実行できないGitHub Actions専用なのでなぜか通常テストも実行できてないここでのタスクにはちょうどいい。
- [x] docs/jp/内にユーザー向けの日本語ドキュメントを作成していく。GitHub用のドキュメントは十分なので完了。続きはドキュメントサイトで作成。
- [ ] Laravel版エンジンAPI用に`docs/jp/laravel-openapi.json`を調整。`docs/en/laravel-openapi.json`は英語に翻訳。別のドキュメントサイトは`openapi.json`からAPIページを作成できるのでそれ用。基本的にはそのままでdescriptionにLaravelでの対応状況を説明。`docs/en/laravel-openapi.json`に日本語が残ってるので英語に翻訳、英語での説明が難しい場合は短く省略していい。
- [x] Laravel版エンジンAPI用に`resources/engine_manifest.json`を調整。VOICEVOX公式はマルチエンジンを想定して非対応機能があっても大丈夫なようにしている。最低限`/audio_query`と`/synthesis`さえ対応していればVOICEVOXエンジン。
- [x] EngineInstallCommand.php を [Laravel Prompts](https://laravel.com/docs/13.x/prompts) を使うコードに変更。GitHubからのダウンロードはzipで400MB以上なので`confirm()`で確認を入れる。ダウンロード中はspinもしくはtaskで表示。
- [x] AI SDKへのソング機能実装は不要。
- [x] README.mdはAWからは変更が制限されているので更新は不要。
- 現在のAgentic Workflowsは手動実行のみ
