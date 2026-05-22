# 設定リファレンス

このドキュメントでは、`config/voicevox.php` の全設定項目について詳しく説明します。

## 設定ファイルの公開

```bash
php artisan vendor:publish --tag="voicevox-config"
```

このコマンドで `config/voicevox.php` が作成されます。

## 設定項目

### クライアントモード設定

クライアントモード（HTTP で公式 VOICEVOX エンジンにアクセス）に関する設定です。

#### `client.url`

**タイプ**: `string`  
**デフォルト**: `'http://127.0.0.1:50021'`  
**環境変数**: `VOICEVOX_URL`

公式 VOICEVOX エンジン API の URL を指定します。

**設定例**:

```php
// config/voicevox.php
'client' => [
    'url' => env('VOICEVOX_URL', 'http://127.0.0.1:50021'),
],
```

```env
# .env
VOICEVOX_URL=http://127.0.0.1:50021
```

**用途**:
- ローカルで Docker 起動した公式エンジンに接続
- リモートの VOICEVOX エンジンに接続

**Docker での公式エンジン起動例**:

```bash
docker pull voicevox/voicevox_engine:cpu-latest
docker run --rm -p '127.0.0.1:50021:50021' voicevox/voicevox_engine:cpu-latest
```

#### `client.core_version`

**タイプ**: `string|null`  
**デフォルト**: `null`  
**環境変数**: `VOICEVOX_CLIENT_CORE_VERSION`

エンジン API で指定するコアバージョン。通常は指定不要です。

**設定例**:

```php
'client' => [
    'core_version' => env('VOICEVOX_CLIENT_CORE_VERSION'),
],
```

```env
# .env
VOICEVOX_CLIENT_CORE_VERSION=0.15.0
```

**用途**:
- 特定バージョンのコアを使いたい場合
- マルチバージョン対応のエンジンで明示的にバージョン指定

### ネイティブモード設定

ネイティブモード（FFI で VOICEVOX CORE を直接呼び出し）に関する設定です。

#### `core.path`

**タイプ**: `string|null`  
**デフォルト**: `null`  
**環境変数**: `VOICEVOX_CORE_PATH`  
**必須**: ネイティブモード使用時

VOICEVOX CORE ライブラリのインストールパスを指定します。

**設定例**:

```php
// config/voicevox.php
'core' => [
    'path' => env('VOICEVOX_CORE_PATH'),
],
```

```env
# .env (macOS)
VOICEVOX_CORE_PATH=/Users/username/.local/voicevox_core/

# .env (Linux)
VOICEVOX_CORE_PATH=/home/username/.local/voicevox_core/

# .env (Windows)
VOICEVOX_CORE_PATH=C:\Users\username\.local\voicevox_core\
```

**インストール方法**:

VOICEVOX CORE のインストール手順は [voicevox-core-php の README](https://github.com/invokable/voicevox-core-php#installation) を参照してください。

#### `core.dict`

**タイプ**: `string`  
**デフォルト**: `'dict/open_jtalk_dic_utf_8-1.11'`  
**環境変数**: `VOICEVOX_CORE_DICT_PATH`

VOICEVOX CORE 内の OpenJTalk 辞書の相対パスを指定します。

**設定例**:

```php
'core' => [
    'dict' => env('VOICEVOX_CORE_DICT_PATH', 'dict/open_jtalk_dic_utf_8-1.11'),
],
```

**通常は変更不要です。** VOICEVOX CORE に同梱されている辞書を使用します。

#### `core.models`

**タイプ**: `string`  
**デフォルト**: `'models/vvms'`  
**環境変数**: `VOICEVOX_CORE_MODELS_PATH`

VOICEVOX CORE 内のモデルディレクトリの相対パスを指定します。

**設定例**:

```php
'core' => [
    'models' => env('VOICEVOX_CORE_MODELS_PATH', 'models/vvms'),
],
```

**通常は変更不要です。** VOICEVOX CORE に同梱されているモデルディレクトリを使用します。

#### `core.user_dict`

**タイプ**: `string`  
**デフォルト**: `storage_path('voicevox/user_dict.json')`  
**環境変数**: `VOICEVOX_CORE_USER_DICT_PATH`

ユーザー辞書ファイルの保存パスを指定します。

**設定例**:

```php
'core' => [
    'user_dict' => env('VOICEVOX_CORE_USER_DICT_PATH', storage_path('voicevox/user_dict.json')),
],
```

```env
# .env
VOICEVOX_CORE_USER_DICT_PATH=/path/to/custom/user_dict.json
```

**用途**:
- カスタムパスにユーザー辞書を保存
- 複数プロジェクトで辞書を共有

詳細: [ユーザー辞書](user-dict.md)

#### `core.presets`

**タイプ**: `string`  
**デフォルト**: `storage_path('voicevox/presets.json')`  
**環境変数**: `VOICEVOX_CORE_PRESETS_PATH`

プリセットファイルの保存パスを指定します。

**設定例**:

```php
'core' => [
    'presets' => env('VOICEVOX_CORE_PRESETS_PATH', storage_path('voicevox/presets.json')),
],
```

```env
# .env
VOICEVOX_CORE_PRESETS_PATH=/path/to/custom/presets.json
```

**用途**:
- カスタムパスにプリセットを保存
- 複数プロジェクトでプリセットを共有

詳細: [プリセット](presets.md)

#### `core.vvms`

**タイプ**: `array`  
**デフォルト**: `['0.vvm', '9.vvm', 's0.vvm']`  
**環境変数**: なし（PHP 配列のため）

起動時に読み込むモデルの配列を指定します。空配列 `[]` を指定すると全モデルを読み込みますが、起動が遅くなります。

**設定例**:

```php
// デフォルト（高速起動）
'core' => [
    'vvms' => ['0.vvm', '9.vvm', 's0.vvm'],
],

// 全モデルを読み込む（起動が遅い）
'core' => [
    'vvms' => [],
],

// 歌唱用のみ
'core' => [
    'vvms' => ['s0.vvm'],
],
```

### エンジンAPIモード設定

エンジンAPIモード（Laravel を VOICEVOX エンジンとして公開）に関する設定です。

#### `engine.disabled`

**タイプ**: `bool`  
**デフォルト**: `false`  
**環境変数**: `VOICEVOX_ENGINE_DISABLED`

エンジン API 機能を無効化するかどうかを指定します。

**設定例**:

```php
'engine' => [
    'disabled' => env('VOICEVOX_ENGINE_DISABLED', false),
],
```

```env
# .env（エンジンAPIを無効化）
VOICEVOX_ENGINE_DISABLED=true
```

**用途**:
- エンジン API を使わない環境で機能を無効化
- セキュリティのため外部からのアクセスを防ぐ

#### `engine.fallback_url`

**タイプ**: `string`  
**デフォルト**: `'http://127.0.0.1:50021'`  
**環境変数**: `VOICEVOX_ENGINE_FALLBACK_URL`

Laravel エンジンが対応していない API エンドポイントを公式エンジンにフォールバックする URL を指定します。

**設定例**:

```php
'engine' => [
    'fallback_url' => env('VOICEVOX_ENGINE_FALLBACK_URL', 'http://127.0.0.1:50021'),
],
```

```env
# .env
VOICEVOX_ENGINE_FALLBACK_URL=http://127.0.0.1:50021
```

**フォールバック対象のエンドポイント**:
- `/synthesis_morphing` (モーフィング合成)
- `/connect_waves` (音声接続)
- `/manage_library` (ライブラリ管理)
- その他、VOICEVOX CORE に相当機能がないエンドポイント

#### `engine.fallback_error`

**タイプ**: `string`  
**デフォルト**: `'The Laravel version of the engine does not support this endpoint. Please use the official engine instead.'`

フォールバックしたが公式エンジンが起動していない場合のエラーメッセージを指定します。

**設定例**:

```php
'engine' => [
    'fallback_error' => 'The Laravel version of the engine does not support this endpoint. Please use the official engine instead.',
],
```
