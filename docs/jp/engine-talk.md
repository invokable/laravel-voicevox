# エンジン API モード：トーク（テキスト音声合成）

Laravel 版エンジン API は、公式 VOICEVOX エンジンと互換性のある HTTP API を Laravel アプリ内に組み込みます。`php artisan serve --port=50513` でサーバーを起動するだけで、公式エンジンと同じエンドポイントが使えます。

## 事前準備

1. `voicevox-core-php` をインストール済みであること
2. VOICEVOX CORE 動的ライブラリをセットアップ済みであること（[インストール手順](installation.md) を参照）
3. `.env` でコアのパスを設定済みであること

```dotenv
VOICEVOX_CORE_PATH=/path/to/voicevox_core/
```

4. `php.ini` で FFI を有効化

```ini
ffi.enable=true
```

5. キャラクター情報リソースをインストール（`/speaker_info` 等を使う場合）

```shell
php artisan voicevox:install
```

## Laravel 版エンジンの起動

```shell
php artisan serve --port=50513
```

デフォルトでは `http://127.0.0.1:50513` で起動します。

### エンジン API の有効・無効

`config/voicevox.php` でエンジンルートの登録を制御できます。

```php
'engine' => [
    'disabled' => env('VOICEVOX_ENGINE_DISABLED', false),
],
```

## トーク音声の生成

公式 VOICEVOX エンジンと同じ手順で音声を生成します。

### 1. audio_query の作成

```shell
curl -s -X POST "http://127.0.0.1:50513/audio_query?speaker=1&text=ララベルが好きなのだ" \
  -H "Content-Type: application/json" \
  > audio_query.json
```

### 2. synthesis で音声合成

```shell
curl -s -X POST "http://127.0.0.1:50513/synthesis?speaker=1" \
  -H "Content-Type: application/json" \
  -d @audio_query.json \
  > talk.wav
```

## Laravel クライアントから使う

Laravel 版エンジンに向けてクライアントモードから接続することもできます。

```php
// config/voicevox.php
'client' => [
    'url' => env('VOICEVOX_URL', 'http://127.0.0.1:50513'), // Laravel エンジンの URL
],
```

```php
use Revolution\Voicevox\Voicevox;

$response = Voicevox::talk('ララベルが好きなのだ', id: 1)->generate(id: 1);

$response->storeAs('engine', 'talk.wav');
```

## 公式エンジンへのフォールバック

一部のエンドポイントは公式エンジンの機能に依存するため、Laravel 版では対応していません。これらのエンドポイントは公式エンジンにフォールバックします。

```php
'engine' => [
    'fallback_url' => env('VOICEVOX_ENGINE_FALLBACK_URL', 'http://127.0.0.1:50021'),
],
```

### トーク関連の対応状況

| エンドポイント | Laravel 版 | フォールバック | 備考 |
|---|---|---|---|
| `POST /audio_query` | ✅ | ✅ | `enable_katakana_english` 非対応 |
| `POST /accent_phrases` | ✅ | ✅ | `enable_katakana_english` 非対応 |
| `POST /synthesis` | ✅ | ✅ | |
| `POST /mora_data` | ✅ | ✅ | |
| `POST /mora_length` | ✅ | ✅ | |
| `POST /mora_pitch` | ✅ | ✅ | |
| `GET /speakers` | ✅ | ✅ | |
| `GET /speaker_info` | ✅ | ✅ | リソースインストールが必要 |
| `POST /cancellable_synthesis` | ❌ | ✅ | フォールバックのみ |
| `POST /multi_synthesis` | ❌ | ✅ | フォールバックのみ |

詳細な対応表は [engine-api.md](../engine-api.md) を参照してください。

## クライアントモード・ネイティブモードとの違い

| 項目 | エンジン API | クライアントモード | ネイティブモード |
|---|---|---|---|
| アクセス方法 | HTTP API として提供 | 外部エンジンに HTTP アクセス | FFI で直接呼び出し |
| FFI | 必要（サーバー側） | 不要 | 必要 |
| 英語カタカナ変換 | なし（フォールバック先にあり） | あり | なし |
