# ネイティブ版

クライアントとは違うPHP版コアを使う場合の使い方案。最近の公式に合わせてトークとソングを2大機能のように扱う。

- src/Talk/Talk.php: `Talk::make()->talk(text:)->generate()`。`Talk::fake()`でテスト用にモック。
- src/Song/Song.php: `Song::make()->song(score:)->generate()`
- src/Engine/: 他の機能は仮でEngine内に配置。
- functions.php: `talk()`, `song()`。Talk、Songクラスは実際には関数から使う。Laravel AI SDKの`agent()`やLaravel Promptsと同じ実装パターン。

```php
use function Revolution\Voicevox\talk;

$response = talk(text: 'ララベルが好きなのだ', id: 1)->generate();
$response->storeAs('talk.wav');
```

```php
use Revolution\Voicevox\Song\SongAudioQuery;
use function Revolution\Voicevox\song;

$response = song($score)
              ->tap(function (SongAudioQuery $song) {
                  $song->score; // scoreを変更後f0→volumeの順で更新する必要がある。
                  $song->sync();
              })->generate();

$response->storeAs('song.wav');
```

主要機能の`talk()`, `song()`はクライアントの`Voicevox::talk()`から`Voicevox::`を消せば移行できるようにしておく。他も全部揃えるのは難しい。

### 仮の機能リスト

VoicevoxClientと同等の機能は一通り用意したいけど対応不可な機能も多い。`_`を使わない簡潔な関数名が理想。

```php
use function Revolution\Voicevox\{talk, kana, song, dict, preset};

// コア機能のみなので英語からカタカナ変換がないなどエンジンAPIとは少し違う。
// 事前にLLMでカタカナに変換すれば同じ結果。
talk($text, id: $id)->generate($id);

// AquesTalk風記法カタカナからならエンジンAPIと同等の合成結果。
kana($kana, id: $id)->generate($id);

song($score, teacher: $teacher)->generate($id);

dict()->all();
dict()->add();
dict()->update();
dict()->delete();
// dict()->import();

preset()->all();
preset()->find();
preset()->add();
preset()->update();
preset()->delete();
```

### Testing

`Talk::fake()`で実現したかったけど実装忘れのまま。

これでモックできるのでひとまずはこの方法。

```php
use Revolution\Voicevox\Talk\Talk;

$this->mock(Talk::class, function ($mock) {
    $mock->allows('talk->generate')->andReturn('wav');
});
```
