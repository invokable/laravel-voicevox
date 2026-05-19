# クライアントモード：トーク（テキスト音声合成）

クライアントモードは公式 VOICEVOX エンジンに HTTP リクエストを送信して音声を生成します。FFI 不要で、Docker で公式エンジンを起動するだけで使えます。

## 事前準備

Docker で公式 VOICEVOX エンジンを起動します。

```shell
docker pull voicevox/voicevox_engine:cpu-latest
docker run --rm -p '127.0.0.1:50021:50021' voicevox/voicevox_engine:cpu-latest
```

GPU が使える環境なら GPU 版も利用できます。

## 基本的な使い方

`Voicevox` Facade の `talk()` メソッドでテキストから音声を生成します。

```php
use Revolution\Voicevox\Voicevox;

$response = Voicevox::talk('Laravelが好きなのだ')->generate();

// Storage に保存
$response->storeAs('client', 'talk.wav');
```

## スタイル ID の指定

`talk()` と `generate()` の両方に話者のスタイル ID を指定できます。  
`talk()` の `id` は `audio_query` 生成時のスタイル、`generate()` の `id` は音声合成時のスタイルです。  
通常は同じ ID を指定します。

```php
use Revolution\Voicevox\Voicevox;

// id: 1 = ずんだもん（あまあま）
$response = Voicevox::talk('ずんだもんなのだ', id: 1)->generate(id: 1);

$response->storeAs('client', 'talk.wav');
```

利用可能なスピーカーとスタイルの一覧は `speakers()` で取得できます。

```php
$speakers = Voicevox::speakers();
```

## tap() で音声パラメーターを調整

`tap()` を使ってオーディオクエリーのパラメーターを調整できます。

```php
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\Client\TalkAudioQuery;

$response = Voicevox::talk('速めに話すのだ', id: 1)
    ->tap(function (TalkAudioQuery $talk) {
        $talk->audioQuery['speedScale'] = 1.2;   // 話速（デフォルト: 1.0）
        $talk->audioQuery['pitchScale'] = 0.05;  // 音高（デフォルト: 0.0）
        $talk->audioQuery['intonationScale'] = 1.5; // 抑揚（デフォルト: 1.0）
        $talk->audioQuery['volumeScale'] = 1.0;  // 音量（デフォルト: 1.0）
    })
    ->generate(id: 1);

$response->storeAs('client', 'talk.wav');
```

## 英語のカタカナ自動変換

クライアントモードでは `enable_katakana_english` が有効になっているため、英語のテキストが自動的にカタカナに変換されます。

```php
// "Laravel" → "ララベル" のように自動変換される
$response = Voicevox::talk('I love Laravel', id: 1)->generate(id: 1);
```

無効にしたい場合は第3引数を `false` にします。

```php
$response = Voicevox::talk('I love Laravel', id: 1, enableKatakanaEnglish: false)->generate(id: 1);
```

## レスポンスの操作

`generate()` が返す `VoicevoxResponse` には以下のメソッドがあります。

```php
$response = Voicevox::talk('テスト', id: 1)->generate(id: 1);

// 音声データのバイト列を取得
$wav = $response->content();

// Laravel Storage に保存（パスを返す）
$path = $response->storeAs('client', 'talk.wav');

// Storage::disk() を指定して保存
$path = $response->storeAs('client', 'talk.wav', disk: 's3');
```

## メソッドチェーン全体の流れ

```
Voicevox::talk($text, id: $speakerId)
    → TalkAudioQuery（audio_query の結果を保持）
        → tap() でパラメーター調整（任意）
        → generate(id: $speakerId)
            → VoicevoxResponse（WAV 音声データ）
                → storeAs() / content()
```
