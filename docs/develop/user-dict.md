# ユーザー辞書

## クライアントモード

公式エンジンのDocker内に`user_dict.json`が保存されているはず。

公式の説明通りに使える。

```php
use Revolution\Voicevox\Voicevox;

$dict = Voicevox::userDict();

Voicevox::addWord(
    surface: 'ボイボ', // 辞書に登録する単語
    pronunciation: 'ボイボ', // カタカナでの読み方
    accentType: 1, // アクセント核位置、整数
);
```

登録する単語は英語とは限らずアクセント位置を登録するために日本語を登録する使い方も可能。

登録した辞書は普通に`Voicevox::talk()`を使う時に適用される。

## ネイティブモード

`config/voicevox.php`で設定した場所（デフォルトはLaravelのstorage内）にuser_dict.jsonを作る。

コアの機能を使っているので保存場所が違う以外は同じ。

```php
use function Revolution\Voicevox\dict;

$dict = dict()->all();

dict()->add(
    surface: 'Laravel', // 辞書に登録する単語
    pronunciation: 'ララベル', // カタカナでの読み方
    accentType: 1, // アクセント核位置、整数
);
```

ネイティブモードでも登録した辞書は自動的に適用される。

## エンジンAPI

`user_dict.json`の保存場所が違うのでユーザー辞書関連APIでは公式エンジンにフォールバックせずネイティブモードと同じ辞書が使われる。

`/audio_query`エンドポイントではネイティブが使われる時はネイティブの辞書、フォールバックした時は公式エンジンの辞書が使われる。

## エクスポート・インポート

普通のjsonなのでクライアント・ネイティブ・エンジンのそれぞれの機能で辞書をjsonで保存すればエクスポート、jsonを読み込めばインポート。
