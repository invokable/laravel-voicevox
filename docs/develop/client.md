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
$response = Voicevox::song(score: ['notes' => []], teacher: 6000)->generate(id: 3001);
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
song(Score|array $score, int|string $teacher) {
    $score = $score instanceOf Arrayable ? $score->toArray(): $score;
}
```

エンジンAPIと一対一なsingFrameAudioQueryで作られたけどtalkと同じ使い方になるようsongに変更。  
singFrameAudioQueryも残してaudioQueryも追加して、talkとsongはメソッドチェーンの開始地点になるようにした。

```php
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\Client\SongAudioQuery;

$response = Voicevox::song($score, teacher: 6000) // sing_frame_audio_queryでframe_audio_queryを生成
            ->tap(function(SongAudioQuery $song) {
                // sing_frame_f0やsing_frame_volumeは最初にframe_audio_queryを作った後の調整用。

                // 1. $song->scoreのnoteのkeyなどを変更したら
                // 2. f0を変更
                $f0 = Voicevox::singFrameF0($song->score, $song->frameAudioQuery, $song->teacher);
                $song->frameAudioQuery['f0'] = $f0;
                // 3. volumeを変更。必ずf0→volumeの順番で変更する。
                $volume= Voicevox::singFrameVolume($song->score, $song->frameAudioQuery, $song->teacher);
                $song->frameAudioQuery['volume'] = $volume;

                // Voicevox::を見せない更新メソッド
                $song->updateF0();
                $song->updateVolume();

                // さらにf0とvolumeの更新をまとめて行うsync()
                $song->sync()
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
