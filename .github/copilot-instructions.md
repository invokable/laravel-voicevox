# Project Guidelines

## Overview

[VOICEVOX](https://github.com/VOICEVOX) for Laravel.

GitHub Agentic Workflowsを使って少しずつ実装を進める。

## VOICEVOXの構成

- [VOICEVOXエディター](https://github.com/VOICEVOX/voicevox)：GUIアプリケーション。このプロジェクトでは関係ない。Electron、TypeScript、Vue。
- [VOICEVOXエンジン](https://github.com/VOICEVOX/voicevox_engine)：Webサーバーとして提供されるテキスト音声合成 API。Python、FastAPI、OpenJTalk。
- [VOICEVOXコア](https://github.com/VOICEVOX/voicevox_core)：音声合成の動的ライブラリ。Rust、onnxruntime。C APIの動的ライブラリ（.so/.dll/.dylib）がある。

VOICEVOXエンジンのAPIを呼び出すサードパーティ製のクライアントパッケージ。
https://github.com/voicevox-client

このプロジェクトでまず作るのはこれと同じVOICEVOXクライアントのLaravel版。

### OpenAPI

- .github/openapi.json

## Technology Stack

- **Language**: PHP 8.3+
- **Framework**: Laravel 12.x+
- **Testing**: Pest PHP 4.x
- **Code Quality**: Laravel Pint (PSR-12)

## Command
- `composer run test` - Run pest tests.
- `composer run lint` - Run pint code formatter.

Agentic Workflows環境でも`setup-php`でPHPはインストールされてるはずだけど動かなくても後で通常のGitHub Actionsでtestとlintが実行される。

開発環境での手動動作テスト用コマンドは`workbench/routes/console.php`に実装。

## コーディングガイドライン

- VOICEVOXエンジンのAPIと一対一の対応ではなくLaravelスタイルのクラス名やメソッド名を使う。
- POSTメソッドでもクエリーパラメーターで渡す値と、リクエストボディで渡すJSONが混在しているので実装時には注意する。
- VOICEVOXは歴史的経緯によりSpeakerId(UUID) と StyleId(整数)が混同している。後発のLaravel版では気にしなくていいので将来的に変更されてもいいように引数では`int|string $id`とする。
- VOICEVOXエンジンやコア内部の命名は、テキスト音声合成は`talk`や`tts`、歌声音声合成は`song`が使われている模様。公開APIは変更しにくいけど内部は変更できるので後からでも変わっている。

## VOICEVOX クライアント

想定している使い方は、
公式のVOICEVOXエンジンをDockerで動かしてウェブサーバーを起動、LaravelのHttpクライアントで`http://127.0.0.1:50021`にリクエストを送信。

```shell
docker pull voicevox/voicevox_engine:cpu-latest
docker run --rm -p '127.0.0.1:50021:50021' voicevox/voicevox_engine:cpu-latest
```

公式のVOICEVOXエンジンは最初からずんだもんボイスがセットアップされてるので [README](https://github.com/VOICEVOX/voicevox_engine/blob/master/README.md) 通りに`audio_query`と`synthesis`に2回リクエストするだけで音声ファイルを生成できる。
ローカルで動かすだけなら簡単。クライアントを作るのも簡単なのでひとまずここから。

名前空間：`Revolution\Voicevox`

- src/Client/VoicevoxClient.php: メインのクライアントクラス。`voice($text, $id): VoiceAudioQuery`で`audio_query`を実行。
- src/Client/VoiceAudioQuery.php: VoiceのAudioQueryクラス。`audio_query`の結果のjsonを保持して、`synthesis`を実行。`generate($id = 1): VoiceResponse`
- src/Client/VoiceResponse.php: `synthesis`の結果の音声の生データを保持するレスポンス。
- src/Voicevox.php: Facade。interfaceなしで直接VoicevoxClientを指定。最近のLaravel公式に多い書き方。
- src/Ai/: [Laravel AI SDK](https://github.com/laravel/ai) 連携。AI SDKのAudioを使った音声合成を実装。`Audio::of('I love coding with Laravel.')->generate();`。そもそもAI SDKに近い使い方で作る。
- src/VoicevoxServiceProvider.php: `$this->app->scoped(VoicevoxClient::class`でVoicevoxClientを初期化。
- config/voicevox.php: `'url' => env('VOICEVOX_URL','http://127.0.0.1:50021'),`

最終的な使い方のイメージ
```php
use Revolution\Voicevox\Voicevox;

$response = Voicevox::voice('ララベルが好きなのだ')->generate();

Storage::put('voice.wav', $response->content());
$response->storeAs('voice.wav');
```

VoiceAudioQueryに`Tappable`トレイトを追加して途中での調整を可能にする。
```php
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\Client\VoiceAudioQuery;

$response = Voicevox::voice('タップで調整できるのだ')
    ->tap(function(VoiceAudioQuery $voice) {
        $voice->audio_query['speedScale'] = 1.2;
    })
    ->generate();

$response->storeAs('voice.wav');
```

あくまでも初期のシンプルな使い方前提なので他の機能を追加していったらクラス名、メソッド名が変わる可能性はある。

音声生成まではAIなしでも手動でさっと実装できたのでクライアントは難しくない。
基本的な設計は決まったので後はGitHub Agentic Workflowsで継続。

歌声はこんなコード。
```php
$response = Voicevox::song(score: ['notes' => []], id: 6000)->generate(id: 3001);
```
歌声機能のコアへの追加は最近のようなので調査が必要。`score`を配列で渡すか`Score`や`Note`のクラスを作るかどうか。現実的には両方対応。
```php
$score = new Score(notes: [
    new Note(),
    new Note(),
]);
```
```php
song(Score|array $score, int|string $id) {
    $score = $score instanceOf Arrayable ? $score->toArray(): $score;
}
```

## speaker id

`$speakers = Voicevox::speakers()`で得られるスピーカーリストのスタイル内にあるidを指定する。id=1はずんだもんのあまあま。本来はStyleIdだけどエンジンAPIではspeakerにStyleIdを渡している。

```json
  {
    "name": "ずんだもん",
    "speaker_uuid": "388f246b-8c41-4ac1-8e2d-5d79f3ff56d9",
    "styles": [
      {
        "name": "ノーマル",
        "id": 3,
        "type": "talk"
      },
      {
        "name": "あまあま",
        "id": 1,
        "type": "talk"
      },
      {
        "name": "ツンツン",
        "id": 7,
        "type": "talk"
      },
```

## 将来的な計画

今の所計画はなく環境が変わったらの話。

- VOICEVOXコア：RubyやGoなどの各言語版のFFIラッパーが作られているのでPHPのFFIでも同じように実装は可能なはず。実装はできてもPHPの場合は動かす環境に課題がある。PHPではFFIは無効にされていることが多い。何よりLaravel Cloudで無効なので実装しても簡単に使える環境を用意できない。homebrew/MacやWSL/WindowsのPHPならFFIが有効なので「ローカル限定」なら可能かもしれない。
- VOICEVOXエンジン：コアの移植さえできればエンジンは簡単。別に作る必要もなくパッケージ内からルートを提供できる。
- VOICEVOXアプリ：ローカル限定でもいいのでエディターも実装。
- 他言語版のFFIラッパーを見てもローカルに動的ライブラリをインストール、もしくはコンパイルするアプリを想定している。
- [NativePHP](https://github.com/nativephp) でデスクトップアプリを作る場合は内部で [static-php-cli](https://github.com/crazywhalecc/static-php-cli) が使われているので動的ライブラリをFFIで使う方法で実装可能。
- [ext-php-rs](https://github.com/extphprs/ext-php-rs) でRustからPHP拡張を作ればstatic-php-cliに拡張を動的リンクしてビルド可能かもしれない。OSごとに異なる。この辺りは要調査。
