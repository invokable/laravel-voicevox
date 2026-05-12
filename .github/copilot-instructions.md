# VOICEVOX for Laravel Project Guidelines

## Overview

[VOICEVOX](https://github.com/VOICEVOX) for Laravel.

全てローカルで動かす前提。Webサーバーとして動かすのは難しいので優先度は低い。

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
- [voicevox-core-php](https://github.com/invokable/voicevox-core-php)

## Command
- `composer run test` - Run pest tests.
- `composer run lint` - Run pint code formatter.

Agentic Workflows環境でも`setup-php`でPHPはインストールされてるはずだけど動かなくても後で通常のGitHub Actionsでtestとlintが実行される。

動的ライブラリ込みのテストはGitHub Actions専用に`test:integration`と`.github/workflows/integration-tests.yml`を用意する。voicevox-core-phpと同じ。

開発環境での手動動作テスト用コマンドは`workbench/routes/console.php`に実装。

## コーディングガイドライン

- VOICEVOXエンジンのAPIと一対一の対応ではなくLaravelスタイルのクラス名やメソッド名を使う。
- POSTメソッドでもクエリーパラメーターで渡す値と、リクエストボディで渡すJSONが混在しているので実装時には注意する。
- VOICEVOXは歴史的経緯によりSpeakerId(UUID) と StyleId(整数)が混同している。後発のLaravel版では気にしなくていいので将来的に変更されてもいいように引数では`int|string $id`とする。
- VOICEVOXエンジンやコア内部の命名は、テキスト音声合成は`talk`や`tts`、歌声音声合成は`song`が使われている模様。公開APIは変更しにくいけど内部は変更できるので後からでも変わっている。最新の公式に合わせてLaravel版でもTalkとSongを使う。

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

- src/Client/VoicevoxClient.php: メインのクライアントクラス。`talk($text, $id): TalkAudioQuery`で`audio_query`を実行。
- src/Client/TalkAudioQuery.php: TalkのAudioQueryクラス。`audio_query`の結果のjsonを保持して、`synthesis`を実行。`generate($id = 1): TalkResponse`
- src/Client/TalkResponse.php: `synthesis`の結果の音声の生データを保持するレスポンス。
- src/Voicevox.php: Facade。interfaceなしで直接VoicevoxClientを指定。最近のLaravel公式に多い書き方。
- src/Ai/: [Laravel AI SDK](https://github.com/laravel/ai) 連携。AI SDKのAudioを使った音声合成を実装。`Audio::of('I love coding with Laravel.')->generate();`。`VoicevoxProvider.php`と`VoicevoxGateway.php`を作成。AI SDKカスタムプロバイダーは他でも作ってるので間違っても修正できる。
- src/VoicevoxServiceProvider.php: `$this->app->scoped(VoicevoxClient::class`でVoicevoxClientを初期化。
- config/voicevox.php: `'url' => env('VOICEVOX_URL','http://127.0.0.1:50021'),`

最終的な使い方のイメージ
```php
use Revolution\Voicevox\Voicevox;

$response = Voicevox::talk('ララベルが好きなのだ')->generate();

Storage::put('talk.wav', $response->content());
$response->storeAs('talk.wav');
```

TalkAudioQueryに`Tappable`トレイトを追加して途中での調整を可能にする。

```php
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\Client\TalkAudioQuery;

$response = Voicevox::talk('タップで調整できるのだ')
    ->tap(function(TalkAudioQuery $talk) {
        $talk->audio_query['speedScale'] = 1.2;
    })
    ->generate();

$response->storeAs('talk.wav');
```

あくまでも初期のシンプルな使い方前提なので他の機能を追加していったらクラス名、メソッド名が変わる可能性はある。

音声生成まではAIなしでも手動でさっと実装できたのでクライアントは難しくない。
基本的な設計は決まったので後はGitHub Agentic Workflowsで継続。

歌声はこんなコード。
```php
$response = Voicevox::song(score: ['notes' => []], id: 6000)->generate(id: 3001);
```
歌声機能のコアへの追加は最近。  
`Score`や`Note`は公式ではコアのPython APIで定義。https://github.com/VOICEVOX/voicevox_core/blob/main/crates/voicevox_core_python_api/python/voicevox_core/_python/__init__.py
Arrayableやvalidate()でLaravelの機能を使いたいのでvoicevox-core-phpではなくLaravel版で実装。クライアント限定ではなく他でも使いそうなのでsrc/Song/Score.phpとNote.phpに作成済み。
```php
$score = new Score(notes: [
    new Note(length: 15, lyric: ''),
    new Note(length: 45, lyric: 'ド', key: 60),
    new Note(length: 45, lyric: 'レ', key: 62),
]);
```
```php
song(Score|array $score, int|string $id) {
    $score = $score instanceOf Arrayable ? $score->toArray(): $score;
}
```

エンジンAPIと一対一なsingFrameAudioQueryで作られたけどtalkと同じ使い方になるようsongに変更。  
singFrameAudioQueryも残してaudioQueryも追加して、talkとsongはメソッドチェーンの開始地点になるようにした。

```php
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\Client\SongAudioQuery;

$response = Voicevox::song($score, id: 6000) // sing_frame_audio_queryでframe_audio_queryを生成
            ->tap(function(SongAudioQuery $song) {
                // sing_frame_f0やsing_frame_volumeは最初にframe_audio_queryを作った後の調整用。

                // 1. $song->scoreのnoteのkeyなどを変更したら
                // 2. f0を変更
                $f0 = Voicevox::singFrameF0($song->score, $song->frame_audio_query, $song->id);
                $song->frame_audio_query['f0'] = $f0;
                // 3. volumeを変更。必ずf0→volumeの順番で変更する。
                $volume= Voicevox::singFrameVolume($song->score, $song->frame_audio_query, $song->id);
                $song->frame_audio_query['volume'] = $volume;
            })
            ->generate(id: 3001); // frame_synthesisで音声を生成

$response->storeAs('song.wav');
```

SongResponseは今はTalkResponseと同じでも別クラスにしておく。

### speaker id

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

## VOICEVOX コア

PHP版のFFIラッパーが作れたので他も可能な範囲で実装していく。
https://github.com/invokable/voicevox-core-php

クライアント機能は今のまま公式VOICEVOXエンジンを使う想定で開発を継続。  
Laravel版エンジンが完成したら変更するかもしれないけどHttpを経由するのが非効率だったら別の実装方法にするかもしれない。

公式エンジンを別で動かせばLaravel版クライアントはFFIなしで使えるメリットがあるので残す理由があった。無FFI環境用にクライアントは後で別パッケージに分離する。この`laravel-voicevox`は全部入り。

## VOICEVOX エンジン

公式互換のWeb APIを作る。Laravelなら当然簡単。このパッケージ内からルートを提供。

Agentic Workflows環境ではコアの動的ライブラリをインストールして実際に動作させることは難しそうなので実装だけして、ローカルで人間が手動で動作確認を行う。`workbench/routes/console.php`にテスト用コマンドを作成。

名前空間：`Revolution\Voicevox\Engine`

- routes/voicevox.php: ルート
- src/Engine/Http/Controllers/: Controllerクラスを配置。一応分かりやすくControllerの名前を付けるけど何も継承しない。`__invoke()`だけのシングルアクションコントローラー、APIリソース、APIシングルトンリソースなどで作成。Controllerファイルは増えてもいいのでAPIごとに分割。
- src/VoicevoxServiceProvider.php: エンジンルートを登録
- config/voicevox.php: 不要な場合もあるだろうからエンジンルートの無効化設定

### 音声モデルファイル(.vvm)とスタイルIDの対応表

コアではvvmを読み込んでからスタイルIDを指定して使う。エンジンAPIではスタイルIDだけなのでどこかでスタイルIDからvvmを取得してるのかも。  
https://github.com/VOICEVOX/voicevox_vvm

## ネイティブ版

クライアントとは違うPHP版コアを使う場合の使い方案。最近の公式に合わせてトークとソングを2大機能のように扱う。

- src/Talk/Talk.php: `Talk::make(text:)->generate()`。`Talk::fake()`でテスト用にモック。
- src/Song/Song.php: `Song::make(score:)->generate()`
- functions.php: `talk()`, `song()`。Talk、Songクラスは実際には関数から使う。Laravel AI SDKの`agnet()`と同じ実装パターン。

```php
use function Revolution\Voicevox\{talk, song};

$response = talk(text: 'ララベルが好きなのだ', id: 1)->generate();
$response->storeAs('talk.wav');
```

クライアントの`Voicevox::talk()`から`Voicevox::`を消せば移行できるようにしておく。

## 将来的な計画

今の所計画はなく環境が変わったらの話。

- ~~VOICEVOXコア：RubyやGoなどの各言語版のFFIラッパーが作られているのでPHPのFFIでも同じように実装は可能なはず。実装はできてもPHPの場合は動かす環境に課題がある。PHPではFFIは無効にされていることが多い。何よりLaravel Cloudで無効なので実装しても簡単に使える環境を用意できない。homebrew/MacやWSL/WindowsのPHPならFFIが有効なので「ローカル限定」なら可能かもしれない。~~
- ~~VOICEVOXエンジン：コアの移植さえできればエンジンは簡単。別に作る必要もなくパッケージ内からルートを提供できる。~~
- VOICEVOXアプリ：ローカル限定でもいいのでエディターも実装。
- 他言語版のFFIラッパーを見てもローカルに動的ライブラリをインストール、もしくはコンパイルするアプリを想定している。
- [NativePHP](https://github.com/nativephp) でデスクトップアプリを作る場合は内部で [static-php-cli](https://github.com/crazywhalecc/static-php-cli) が使われているので動的ライブラリをFFIで使う方法で実装可能。カスタムstatic-php-cliを作る必要がある。
- ~~[ext-php-rs](https://github.com/extphprs/ext-php-rs) でRustからPHP拡張を作ればstatic-php-cliに拡張を動的リンクしてビルド可能かもしれない。OSごとに異なる。この辺りは要調査。~~ FFIで十分動くので拡張は不要そう。
