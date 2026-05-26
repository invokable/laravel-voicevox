# クライアントモード：ユーザー辞書

ユーザー辞書を使うと、固有名詞や専門用語の読み方を公式 VOICEVOX エンジンに登録できます。クライアントモードでは Laravel 側に辞書を保存せず、接続先の公式エンジンが管理するユーザー辞書 API をそのまま利用します。

## 概要

クライアントモードのユーザー辞書は `Voicevox` Facade から操作します。内部では公式 VOICEVOX エンジンの `/user_dict`、`/user_dict_word`、`/import_user_dict` に HTTP リクエストを送信します。

公式エンジンの辞書データは OS ごとのユーザー領域に保存されます。VOICEVOXアプリ（製品版）macOS のビルド版では通常、次の場所に保存されます。

```text
~/Library/Application Support/voicevox-engine/user_dict.json
```

Docker で公式エンジンを起動している場合も、コンテナ内のユーザー領域に保存されます。コンテナを再起動しても同じコンテナを使い続けている間はデータが維持されます。ただし `docker run --rm` のように一時コンテナを都度作成している場合は、コンテナ終了時に辞書が失われます。辞書を確実に永続化したい場合は Docker volume でコンテナ内のユーザー領域をマウントしてください。

ネイティブモードの `storage/voicevox/user_dict.json` とは別管理です。Laravel 側の `config('voicevox.core.user_dict')` はクライアントモードには使われません。

## 事前準備

公式 VOICEVOX エンジンを起動します。

```shell
docker pull voicevox/voicevox_engine:cpu-latest
docker run --rm -p '127.0.0.1:50021:50021' voicevox/voicevox_engine:cpu-latest
```

接続先は `VOICEVOX_URL` で変更できます。

```env
VOICEVOX_URL=http://127.0.0.1:50021
```

## 基本的な使い方

### 単語の追加

`Voicevox::addWord()` で単語を登録できます。

```php
use Revolution\Voicevox\Voicevox;

$uuid = Voicevox::addWord(
    surface: 'Laravel',
    pronunciation: 'ララベル',
    accentType: 0,
);

// クライアントモードでは "-" ありの UUID が返る
// 例: "a1b2c3d4-0000-0000-0000-000000000000"
```

#### パラメータ

| パラメータ | 型 | 説明 |
|---|---|---|
| `surface` | string | 表記（実際の単語の文字列） |
| `pronunciation` | string | 読み方（カタカナのみ） |
| `accentType` | int | アクセント型（0 = 平板、1 以上 = アクセント位置） |
| `wordType` | string\|null | 品詞（`COMMON_NOUN`、`PROPER_NOUN`、`VERB`、`ADJECTIVE`、`SUFFIX`） |
| `priority` | int\|null | 優先度 |

### 全単語の取得

登録されているすべての単語を取得できます。

```php
use Revolution\Voicevox\Voicevox;

$words = Voicevox::userDict();

/*
[
    "a1b2c3d4-0000-0000-0000-000000000000" => [
        "surface" => "Laravel",
        "pronunciation" => "ララベル",
        "accent_type" => 0,
        "word_type" => "COMMON_NOUN",
        "priority" => 5,
    ],
    ...
]
*/
```

### 単語の更新

追加時に返ってきた UUID を指定して更新します。

```php
use Revolution\Voicevox\Voicevox;

Voicevox::updateWord(
    wordUuid: 'a1b2c3d4-0000-0000-0000-000000000000',
    surface: 'Laravel',
    pronunciation: 'ラレベル',
    accentType: 1,
);
```

### 単語の削除

```php
use Revolution\Voicevox\Voicevox;

Voicevox::deleteWord('a1b2c3d4-0000-0000-0000-000000000000');
```

### 辞書のインポート

公式エンジンのユーザー辞書形式の配列をインポートできます。`override: false`（デフォルト）なら既存の単語は保持され、インポートされた単語が追加されます。

```php
use Revolution\Voicevox\Voicevox;

$words = json_decode(file_get_contents('other_user_dict.json'), true);

Voicevox::importUserDict($words, override: false);

// override: true なら既存辞書を置き換える
Voicevox::importUserDict($words, override: true);
```

クライアントモードにはネイティブモードの `dict()->toJson()` に相当する専用メソッドはありません。エクスポートしたい場合は `Voicevox::userDict()` の結果を JSON に保存します。

```php
$json = json_encode(Voicevox::userDict(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

file_put_contents('exported_user_dict.json', $json);
```

## 辞書登録後の音声合成

ユーザー辞書に登録した単語は、公式エンジンで音声合成するときに自動的に参照されます。

```php
use Revolution\Voicevox\Voicevox;

Voicevox::addWord(
    surface: 'Laravel',
    pronunciation: 'ララベル',
    accentType: 0,
);

$response = Voicevox::talk('Laravelで開発するのは楽しいです', id: 1)->generate(id: 1);

$response->storeAs('client', 'laravel.wav');
```

## Engine API との対応

`Voicevox` Facade の各メソッドは、公式エンジンの API に対応しています。

| Laravel | 公式エンジン API |
|---|---|
| `Voicevox::userDict()` | `GET /user_dict` |
| `Voicevox::addWord()` | `POST /user_dict_word` |
| `Voicevox::updateWord()` | `PUT /user_dict_word/{word_uuid}` |
| `Voicevox::deleteWord()` | `DELETE /user_dict_word/{word_uuid}` |
| `Voicevox::importUserDict()` | `POST /import_user_dict` |

## ネイティブモードとの違い

| 項目 | クライアントモード | ネイティブモード |
|---|---|---|
| 利用方法 | `Voicevox` Facade | `dict()` ヘルパー |
| 保存先 | 公式エンジンのユーザー領域 | Laravel の `storage/voicevox/user_dict.json` |
| macOS の例 | `~/Library/Application Support/voicevox-engine/user_dict.json` | `storage/voicevox/user_dict.json` |
| 追加時の UUID | `-` あり | Core からは `-` なし |
| エンジンプロセス | 必要 | 不要（FFI が必要） |

## 注意事項

- `pronunciation` はカタカナで指定してください。
- クライアントモードでは公式エンジン側の辞書が更新されます。Laravel プロジェクトの `storage` には保存されません。
- `docker run --rm` など一時コンテナを都度作成している場合、コンテナ終了時に辞書が失われます。
- 追加時に返る UUID は `a1b2c3d4-0000-0000-0000-000000000000` のようなハイフンあり形式です。

## トラブルシューティング

### 単語が反映されない

1. 公式 VOICEVOX エンジンが起動しているか確認してください。
2. `VOICEVOX_URL` が正しい接続先を指しているか確認してください。
3. 読み方がカタカナになっているか確認してください。
4. Docker で別コンテナを起動し直していないか確認してください。

### 辞書ファイルの場所がわからない

macOS のビルド版では次の場所を確認してください。

```text
~/Library/Application Support/voicevox-engine/user_dict.json
```

Docker では公式エンジンのユーザー領域（コンテナ内）に保存されます。永続化が必要な場合は、コンテナ内のユーザー領域を Docker volume にマウントしてください。
