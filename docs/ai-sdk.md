# Laravel AI SDK Integration

VOICEVOX for Laravel integrates with the [Laravel AI SDK](https://github.com/laravel/ai), allowing you to generate speech via the `Audio` facade.

Two drivers are provided: **client mode** (`voicevox-client`) and **native mode** (`voicevox`).

---

## Client Driver (`voicevox-client`)

Sends HTTP requests to the official VOICEVOX engine. No FFI required.

### Prerequisites

Start the official VOICEVOX engine with Docker:

```shell
docker pull voicevox/voicevox_engine:cpu-latest
docker run --rm -p '127.0.0.1:50021:50021' voicevox/voicevox_engine:cpu-latest
```

### Configuration

Add a `voicevox-client` provider to `config/ai.php`.  
The `key` field holds the engine base URL (default: `http://127.0.0.1:50021`).

```php
// config/ai.php
'providers' => [
    'voicevox-client' => [
        'driver' => 'voicevox-client',
        'key' => env('VOICEVOX_URL', 'http://127.0.0.1:50021'),
    ],
],
```

### Usage

```php
use Laravel\Ai\Audio;

$response = Audio::of('I love coding with Laravel.')
    ->voice('ずんだもん')
    ->generate('voicevox-client');

Storage::put('talk.wav', $response->content());
```

---

## Native Driver (`voicevox`)

Calls VOICEVOX CORE directly via FFI. Requires FFI to be enabled in `php.ini` and the core library configured in `config/voicevox.php`.

### Configuration

Add a `voicevox` provider to `config/ai.php`:

```php
// config/ai.php
'providers' => [
    'voicevox' => [
        'driver' => 'voicevox',
    ],
],
```

### Usage

```php
use Laravel\Ai\Audio;

$response = Audio::of('ネイティブで話すのだ')
    ->voice('ずんだもん')
    ->generate('voicevox');

Storage::put('talk.wav', $response->content());
```

---

## Voice Aliases

Pass a VOICEVOX style ID as a numeric string, or use a named alias in `voice()`.  
When `voice()` is omitted, `default-female` (ID 10) is used.

### Alias Table

| Alias | Style ID | Character |
|---|---|---|
| `ずんだもん` | 1 | Zundamon (Sweet) |
| `ずんだもん/あまあま` | 1 | Zundamon (Sweet) |
| `ずんだもん/ノーマル` | 3 | Zundamon (Normal) |
| `ずんだもん/セクシー` | 5 | Zundamon (Sexy) |
| `ずんだもん/ツンツン` | 7 | Zundamon (Tsundere) |
| `ずんだもん/ささやき` | 22 | Zundamon (Whisper) |
| `ずんだもん/ヒソヒソ` | 38 | Zundamon (Murmur) |
| `四国めたん/あまあま` | 0 | Shikoku Metan (Sweet) |
| `四国めたん` | 2 | Shikoku Metan (Normal) |
| `四国めたん/ノーマル` | 2 | Shikoku Metan (Normal) |
| `四国めたん/セクシー` | 4 | Shikoku Metan (Sexy) |
| `四国めたん/ツンツン` | 6 | Shikoku Metan (Tsundere) |
| `四国めたん/ヒソヒソ` | 37 | Shikoku Metan (Murmur) |
| `春日部つむぎ` | 8 | Kasukabe Tsumugi (Normal) |
| `波音リツ` | 9 | Naminori Ritsu (Normal) |
| `雨晴はう` | 10 | Amehare Hau (Normal) |
| `玄野武宏` | 11 | Kurono Takehiro (Normal) |
| `白上虎太郎` | 12 | Shirakami Kotaro (Normal) |
| `青山龍星` | 13 | Aoyama Ryusei (Normal) |
| `冥鳴ひまり` | 14 | Meinei Himari (Normal) |
| `九州そら` | 16 | Kyushu Sora (Normal) |
| `default-female` | 10 | Amehare Hau (Normal) |
| `default-male` | 12 | Shirakami Kotaro (Normal) |

Any other value is cast to `int` and used as a raw style ID:

```php
// Use raw style ID directly
Audio::of('テスト')->voice('3')->generate('voicevox-client');
```

To get the full list of available speakers and style IDs:

```php
use Revolution\Voicevox\Voicevox;

$speakers = Voicevox::speakers();
```

See also: [voicevox_vvm](https://github.com/VOICEVOX/voicevox_vvm)

---

## Agents

The native driver does not support `enable_katakana_english` (English-to-katakana conversion). Use an AI agent to pre-convert English text to katakana before synthesis.

### KanalizerAgent

Converts English words in Japanese text to katakana — a substitute for VOICEVOX's [kanalizer](https://github.com/VOICEVOX/kanalizer).

```php
use Revolution\Voicevox\Ai\Agents\KanalizerAgent;
use function Revolution\Voicevox\talk;

$result = KanalizerAgent::make()->prompt('KanalizerAgentでEnglishをカタカナに変換するのだ');
// KanalizerAgentでイングリッシュをカタカナに変換するのだ

// LLM output is non-deterministic; consider a human review step before passing to talk()

$response = talk($result['kana'] ?? $result->text, id: 1)->generate(id: 1);
$response->storeAs('native', 'kanalizer.wav');
```
