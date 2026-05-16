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

- クライアント：VOICEVOXエンジンのAPIと一対一の対応ではなくLaravelスタイルのクラス名やメソッド名を使う。
- POSTメソッドでもクエリーパラメーターで渡す値と、リクエストボディで渡すJSONが混在しているので実装時には注意する。
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
- src/Console/: ユーザーが使うartisanコマンド。エンジンで使うresourcesのインストールなど。
- src/VoicevoxResponse.php: 音声の生データを保持するレスポンス。ひとまず全部で共通のVoicevoxResponseを使用。分けた方が良くなったら別クラス化。
- src/VoicevoxServiceProvider.php
- config/voicevox.php
- src/Ai/: [Laravel AI SDK](https://github.com/laravel/ai) 連携。AI SDKのAudioを使った音声合成を実装。`Audio::of('I love coding with Laravel.')->voice('ずんだもん')->generate();`。`voicevox-client`がクライアント版。`voicevox`がネイティブ版。
- voicevox_engine: 公式エンジンのgitサブモジュール
- voicevox_resource: resourcesのサブモジュール。公式エンジンでは[process_voicevox_resource.bash](../voicevox_engine/tools/process_voicevox_resource.bash) などで使用している。必要なファイルだけLaravel側にコピー。`/speaker_info'`や`/singer_info`で必要。
- resources: voicevox_resourceもしくはGitHubからダウンロードしたキャラ情報。500MB以上のサイズなので別途インストール。

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
- src/VoicevoxResponse.php: `synthesis`の結果の音声の生データを保持するレスポンス。
- src/Voicevox.php: Facade。interfaceなしで直接VoicevoxClientを指定。最近のLaravel公式に多い書き方。

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
        $talk->audioQuery['speedScale'] = 1.2;
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
`Score`や`Note`は公式ではコアのPython APIで定義。
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
                $f0 = Voicevox::singFrameF0($song->score, $song->frameAudioQuery, $song->id);
                $song->frameAudioQuery['f0'] = $f0;
                // 3. volumeを変更。必ずf0→volumeの順番で変更する。
                $volume= Voicevox::singFrameVolume($song->score, $song->frameAudioQuery, $song->id);
                $song->frameAudioQuery['volume'] = $volume;
            })
            ->generate(id: 3001); // frame_synthesisで音声を生成

$response->storeAs('song.wav');
```

sing_frame_audio_queryのidは種類が`sing`か`singing_teacher`のスタイルIDを指定できる、が現状6000がsingでこの一つしか存在してない。frame_synthesisには種類が`sing`か`frame_decode`のスタイルIDを指定、他のほとんどのモデルが対象。3001はテキスト音声と同様ずんだもんのあまあま。6000を教師としてframe_audio_queryを作り、他のボイスで生成する流れ。

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

公式エンジンを別で動かせばLaravel版クライアントはFFIなしで使えるメリットがあるので残す理由があった。

名前空間：`Revolution\Voicevox\Core`

## VOICEVOX エンジン

公式互換のWeb APIを作る。  
コアになくてエンジン独自の機能がかなり多いので実際に作るのは簡単ではなさそう。  
[調査結果](../docs/engine-challenges-en.md) から完全な移植は不可能そうなので比較的実現可能性があるソング機能を優先する。エンジンの後に作りたいアプリはソング特化なのでまずはソングからで十分。ソング機能だけならモデルは`s0.vvm`一つだけで読み込みが早いのも都合がいい。ローカル限定なので公式エンジンと併用も妥協案としてあり。

難しいエンドポイントは公式エンジンに委譲→公式エンジンが起動してない場合はエラーレスポンスを返す。  
PHP版コアでも簡単に実装可能な機能はLaravel版で直接対応。  
対応方針は決まったので実装していける。

名前空間：`Revolution\Voicevox\Engine`

- routes/engine.php: ルート
- src/Engine/Http/: Controllerクラスを配置。一応分かりやすくControllerの名前を付けるけど何も継承しない。`__invoke()`だけのシングルアクションコントローラー、APIリソース、APIシングルトンリソースなどで作成。Controllerファイルは増えてもいいのでAPIごとに分割。
- src/VoicevoxServiceProvider.php: エンジンルートを登録
- config/voicevox.php: 不要な場合もあるだろうからエンジンルートの無効化設定
- docs/engine-api.md: エンジンAPIの対応表

```php
return [
    //  他の設定

    'engine' => [
        'disabled' => env('VOICEVOX_ENGINE_DISABLED', false),
    ],
]
```

### ローカルでもウェブサーバーではデフォルト無効

php artisan serveやtestbench serveで使用するにはphp.iniでFFIを有効化する。

```
ffi.enable=true
```

### キャラクター情報

`resources/character_info/`内に公式エンジンと同じリソースが必要。
500MB以上なので別途インストール。Laravelプロジェクト直下ではなく、vendorのこのパッケージ内にダウンロードされる。

ユーザー向け通常のLaravelプロジェクト。
```bash
php artisan voicevox:install
```
開発環境のtestbench。
```bash
vendor/bin/testbench voicevox:install
```

engine_manifest.jsonはインストールなどの変換処理は挟まず公式を参考にLaravel用に作ればいいはず。
`voicevox_engine/engine_manifest.json`

### プリセット機能

公式エンジンでは`presets.yaml`ファイルを使って管理。おそらくDocker内にしかない。Dockerの公式エンジンとLaravelではプリセット設定を共有できないので、SQLiteデータベースを使う完全に別実装でもいいかもしれないけど、それでは`/audio_query_from_preset`が利用できないので常にフォールバックの対応。

### kanalizer

エンジンAPIにある`enable_katakana_english`は英語をカタカナに変換する機能。  
コアにはないので対応できない部分だったけどよく考えたらVOICEVOXの前にLLMを挟む運用でカバーできるかもしれない。

ここに気付いたことで、通常の日本語からAquesTalk風記法カタカナもLLMで変換すればいいのではと閃いて調べたら対応可能そうだった。AquesTalk風記からの音声合成はコアのみで可能。

Laravel AI SDK用のAquesTalk風記法カタカナ変換エージェントのサンプル。他でも同じようなプロンプトで可能なはず。  
`src/Ai/Agents/AquesTalkAgent.php`

KanalizerAgentの方が正常に動くけど漢字までひらがなにしているのでルールの調整は必要かも。
`src/Ai/Agents/KanalizerAgent.php`

### 音声モデルファイル(.vvm)とスタイルIDの対応表

コアではvvmを読み込んでからスタイルIDを指定して使う。エンジンAPIでは全モデルを読み込んでるのでスタイルIDだけで全部使える。全部読み込むと遅いのでconfigで設定できるようにする。  
https://github.com/VOICEVOX/voicevox_vvm

## ネイティブ版

クライアントとは違うPHP版コアを使う場合の使い方案。最近の公式に合わせてトークとソングを2大機能のように扱う。

- src/Talk/Talk.php: `Talk::make()->talk(text:)->generate()`。`Talk::fake()`でテスト用にモック。
- src/Song/Song.php: `Song::make()->song(score:)->generate()`
- src/Engine/: 他の機能は仮でEngine内に配置。
- functions.php: `talk()`, `song()`。Talk、Songクラスは実際には関数から使う。Laravel AI SDKの`agent()`とやLaravel Promptsと同じ実装パターン。

```php
use function Revolution\Voicevox\talk;

$response = talk(text: 'ララベルが好きなのだ', id: 1)->generate();
$response->storeAs('talk.wav');
```

主要機能の`talk()`, `song()`はクライアントの`Voicevox::talk()`から`Voicevox::`を消せば移行できるようにしておく。他も全部揃えるのは難しい。

### 仮の機能リスト

VoicevoxClientと同等の機能は一通り用意したいけど対応不可な機能も多い。`_`を使わない簡潔な関数名が理想。

```php
use function Revolution\Voicevox\{talk, song, dict, kana};

// コア機能のみなので英語からカタカナ変換がないなどエンジンAPIとは少し違う。
talk($text, id: $id)->generate($id);

// AquesTalk風記法カタカナからならエンジンAPIと同等の合成結果。
kana($kana, id: $id)->generate($id);

song($score, teacher: $teacher)->generate($id);

dict()->all();
dict()->add();
dict()->update();
dict()->delete();
dict()->import();
```

プリセットはコアにはなくエンジンの機能なのでなし。

## 将来的な計画

今の所計画はなく環境が変わったらの話。

- ~~VOICEVOXコア：RubyやGoなどの各言語版のFFIラッパーが作られているのでPHPのFFIでも同じように実装は可能なはず。実装はできてもPHPの場合は動かす環境に課題がある。PHPではFFIは無効にされていることが多い。何よりLaravel Cloudで無効なので実装しても簡単に使える環境を用意できない。homebrew/MacやWSL/WindowsのPHPならFFIが有効なので「ローカル限定」なら可能かもしれない。~~
- ~~VOICEVOXエンジン：コアの移植さえできればエンジンは簡単。別に作る必要もなくパッケージ内からルートを提供できる。~~
- VOICEVOXアプリ：ローカル限定でもいいのでエディターも実装。歌声特化。Laravelではないかも。
- 他言語版のFFIラッパーを見てもローカルに動的ライブラリをインストール、もしくはコンパイルするアプリを想定している。
- [NativePHP](https://github.com/nativephp) でデスクトップアプリを作る場合は内部で [static-php-cli](https://github.com/crazywhalecc/static-php-cli) が使われているので動的ライブラリをFFIで使う方法で実装可能。カスタムstatic-php-cliを作る必要がある。
- ~~[ext-php-rs](https://github.com/extphprs/ext-php-rs) でRustからPHP拡張を作ればstatic-php-cliに拡張を動的リンクしてビルド可能かもしれない。OSごとに異なる。この辺りは要調査。~~ FFIで十分動くので拡張は不要そう。
