# ネイティブモード：ユーザー辞書

ユーザー辞書を使うと、固有名詞や専門用語の読み方を VOICEVOX に覚えさせることができます。登録した単語は音声合成時に適切な発音で読み上げられます。

## 概要

Laravel VOICEVOX のユーザー辞書機能は、VOICEVOX Core の UserDict を利用したネイティブ実装です。辞書データは JSON 形式で `storage/voicevox/user_dict.json` に永続化されます。

公式 VOICEVOX エンジンとは独立したストレージを使用するため、Laravel 側で登録した単語は公式エンジン側には反映されません（その逆も同様です）。

## 設定

デフォルトでは `storage/voicevox/user_dict.json` に保存されます。別のパスを使いたい場合は `config/voicevox.php` で変更できます。

```php
// config/voicevox.php

return [
    'core' => [
        'user_dict' => storage_path('voicevox/user_dict.json'),
    ],
];
```

## 基本的な使い方

### 単語の追加

`dict()` ヘルパーの `add()` メソッドで単語を登録できます。

```php
use function Revolution\Voicevox\dict;

// 表記、読み（カタカナ）、アクセント型を指定
$uuid = dict()->add(
    surface: 'Laravel',
    pronunciation: 'ララベル',
    accentType: 0,
);

// "-"なしのUUID が返ってくる。user_dict.jsonでは"-"あり。
// 例: "550e8400e29b41d4a716446655440000"
```

#### パラメータ

- **surface** (string, 必須): 表記（実際の単語の文字列）
- **pronunciation** (string, 必須): 読み方（カタカナのみ）
- **accentType** (int, 必須): アクセント型（0 = 平板、1 以上 = アクセント位置）
- **wordType** (string|null, オプション): 品詞（`COMMON_NOUN`、`PROPER_NOUN`、`VERB`、`ADJECTIVE`、`SUFFIX`）
- **priority** (int|null, オプション): 優先度（デフォルト: 5）

#### アクセント型について

- `0`: 平板アクセント（全体的に平らな音高）
- `1`: 一拍目にアクセント（最初の音節が高い）
- `2`: 二拍目にアクセント
- `3`: 三拍目にアクセント
- ...

実際の単語のアクセント位置に合わせて指定してください。

### 単語の更新

登録済みの単語を更新するには `update()` メソッドを使います。

```php
use function Revolution\Voicevox\dict;

dict()->update(
    wordUuid: '550e8400-e29b-41d4-a716-446655440000',
    surface: 'Laravel',
    pronunciation: 'ラレベル',
    accentType: 1,
);
```

### 単語の削除

単語を削除するには `delete()` メソッドを使います。

```php
use function Revolution\Voicevox\dict;

dict()->delete('550e8400-e29b-41d4-a716-446655440000');
```

### 全単語の取得

登録されているすべての単語を取得できます。

```php
use function Revolution\Voicevox\dict;

$words = dict()->all();
// または
$words = dict()->toArray();

/*
[
    "550e8400-e29b-41d4-a716-446655440000" => [
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

### 辞書のインポート

他の VOICEVOX ユーザー辞書からデータをインポートできます。`override: false`（デフォルト）なら既存の単語は保持され、インポートされた単語が追加されます。

```php
use function Revolution\Voicevox\dict;

// JSON 文字列でインポート
$json = file_get_contents('other_user_dict.json');
dict()->import($json, override: false);

// override: trueなら上書き
dict()->import($json, override: true);
```

### 辞書のエクスポート

辞書全体を JSON 文字列として取得できます。

```php
use function Revolution\Voicevox\dict;

$json = dict()->toJson();

// ファイルに保存
file_put_contents('exported_dict.json', $json);
```

## Engine API 経由でのアクセス

Laravel VOICEVOX のエンジン API を起動している場合、HTTP 経由でもユーザー辞書を操作できます。公式エンジンにフォールバックせずネイティブの辞書を使用します。

### 全単語の取得

```bash
curl http://localhost:50513/user_dict
```

### 単語の追加

```bash
curl -X POST "http://localhost:50513/user_dict_word?surface=Laravel&pronunciation=ララベル&accent_type=0"
```

### 単語の更新

```bash
curl -X PUT "http://localhost:50513/user_dict_word/{uuid}?surface=Laravel&pronunciation=ラレベル&accent_type=1"
```

### 単語の削除

```bash
curl -X DELETE "http://localhost:50513/user_dict_word/{uuid}"
```

### 辞書のインポート

```bash
curl -X POST "http://localhost:50513/import_user_dict?override=false" \
  -H "Content-Type: application/json" \
  -d @other_dict.json
```

`override=true` を指定すると既存の辞書をすべて削除してからインポートします。

## 実用例

### 固有名詞の登録

```php
use function Revolution\Voicevox\dict;

// 人名
dict()->add(
    surface: '田中太郎',
    pronunciation: 'タナカタロウ',
    accentType: 4,
    wordType: 'PROPER_NOUN',
);

// 地名
dict()->add(
    surface: '秋葉原',
    pronunciation: 'アキハバラ',
    accentType: 3,
    wordType: 'PROPER_NOUN',
);

// 企業名
dict()->add(
    surface: '株式会社サンプル',
    pronunciation: 'カブシキガイシャサンプル',
    accentType: 0,
    wordType: 'PROPER_NOUN',
);
```

### 専門用語の登録

```php
use function Revolution\Voicevox\dict;

// IT 用語
dict()->add(
    surface: 'API',
    pronunciation: 'エーピーアイ',
    accentType: 0,
);

dict()->add(
    surface: 'Webhook',
    pronunciation: 'ウェブフック',
    accentType: 3,
);

// 医療用語
dict()->add(
    surface: 'カルテ',
    pronunciation: 'カルテ',
    accentType: 1,
);
```

### 辞書登録後の音声合成

ユーザー辞書に登録した単語は、音声合成時に自動的に参照されます。

```php
use function Revolution\Voicevox\{dict, talk};

dict()->add(
    surface: 'Laravel',
    pronunciation: 'ララベル',
    accentType: 0,
);

// 登録した読み方で合成される
$response = talk('Laravelで開発するのは楽しいです', id: 1)->generate(id: 1);
$response->storeAs('native', 'laravel.wav');
```

## 注意事項

- **読み方はカタカナのみ**: `pronunciation` パラメータにはカタカナのみを指定してください。ひらがなや漢字は使用できません。
- **公式エンジンとは独立**: Laravel 版のユーザー辞書と公式 VOICEVOX エンジンのユーザー辞書は別々に管理されます。
- **自動保存**: 単語の追加・更新・削除は即座に JSON ファイルに保存されます。
- **アクセント型**: アクセント型の指定は発音の自然さに影響します。不明な場合は `0`（平板）を試してください。

## トラブルシューティング

### 単語が反映されない

1. ストレージディレクトリの書き込み権限を確認してください
2. `storage/voicevox/user_dict.json` が正しく作成されているか確認してください
3. カタカナの表記が正しいか確認してください

### 辞書ファイルの場所がわからない

```php
$path = config('voicevox.core.user_dict');
echo $path;
```

### 辞書をリセットしたい

辞書ファイルを削除すると、次回から空の辞書が作成されます。

```bash
rm storage/voicevox/user_dict.json
```

または PHP から：

```php
use Illuminate\Support\Facades\Storage;

$path = config('voicevox.core.user_dict');
if (file_exists($path)) {
    unlink($path);
}
```
