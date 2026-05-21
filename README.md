# VOICEVOX for Laravel

[![tests](https://github.com/invokable/laravel-voicevox/actions/workflows/tests.yml/badge.svg)](https://github.com/invokable/laravel-voicevox/actions/workflows/tests.yml)
[![linter](https://github.com/invokable/laravel-voicevox/actions/workflows/lint.yml/badge.svg)](https://github.com/invokable/laravel-voicevox/actions/workflows/lint.yml)
[![Maintainability](https://qlty.sh/badges/cc6e0ee3-e221-4c06-90be-4fd87b79310e/maintainability.svg)](https://qlty.sh/gh/invokable/projects/laravel-voicevox)
[![Code Coverage](https://qlty.sh/badges/cc6e0ee3-e221-4c06-90be-4fd87b79310e/coverage.svg)](https://qlty.sh/gh/invokable/projects/laravel-voicevox)
[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/invokable/laravel-voicevox)

Work In Progress.

## Overview

This package brings [VOICEVOX](https://github.com/VOICEVOX), a Japanese TTS / singing synthesis ecosystem, to Laravel.
You can use both client mode (official VOICEVOX Engine over HTTP) and native mode (direct synthesis through PHP FFI + VOICEVOX Core) with a Laravel-style API.

VOICEVOX is widely used in Japan, and many well-known "Zundamon" voice clips are created with it.

| Feature | Supported | Description |
|---|---|---|
| VOICEVOX Client | ✅ | Client for the official VOICEVOX Engine API. Works without FFI. |
| VOICEVOX Core | ✅ | [voicevox-core-php](https://github.com/invokable/voicevox-core-php) wraps VOICEVOX Core dynamic libraries through FFI. |
| Laravel style | ✅ | Uses a Laravel-friendly API for voicevox-core-php features. |
| Laravel AI SDK Integration | ✅ | Supported from Laravel AI SDK Audio in both client and native modes. |
| VOICEVOX Engine | ⚠️ | Laravel-compatible Engine API implemented with PHP Core, with fallback to official engine for non-portable features. |
| VOICEVOX Editor | ❌ | Not supported. |

## Requirements

- PHP 8.3+
- Laravel 12.x+
- FFI: Required for everything except client-only usage.

FFI is disabled on most web servers (including Laravel Cloud), so this package is mainly intended for **local CLI** usage.

In CLI it is typically enabled by default. If you run local web server processes for the Laravel Engine API, enable FFI in `php.ini`:

```ini
ffi.enable=true
```

## Installation

Install both packages to use VOICEVOX Core features:

```shell
composer require revolution/laravel-voicevox revolution/voicevox-core-php
```

You can install only `laravel-voicevox` for client-only mode:

```shell
composer require revolution/laravel-voicevox
```

## VOICEVOX Core Dynamic Library Setup

To use FFI-based features, install VOICEVOX Core libraries by following the [voicevox-core-php README](https://github.com/invokable/voicevox-core-php/blob/main/README.md).

## Configuration

Publish the package config file (`config/voicevox.php`):

```shell
php artisan vendor:publish --tag="voicevox-config"
```

For Core features, configure the path in `.env`:

```dotenv
VOICEVOX_CORE_PATH=/.../.local/voicevox_core/
```

## Usage

### Client mode

Use client mode through the **Voicevox** facade.

Client mode connects to the [official VOICEVOX Engine](https://github.com/VOICEVOX/voicevox_engine). Start it with Docker (GPU image is also available in supported environments):

```shell
docker pull voicevox/voicevox_engine:cpu-latest
docker run --rm -p '127.0.0.1:50021:50021' voicevox/voicevox_engine:cpu-latest
```

Text-to-speech. Client mode enables `enable_katakana_english`, so English words are automatically converted into katakana.

```php
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\Client\TalkAudioQuery;

$response = Voicevox::talk('Laravelが好きなのだ', id: 1)
    ->tap(function (TalkAudioQuery $talk): void {
        $talk->audioQuery['speedScale'] = 1.2;
    })->generate(id: 1);

$response->storeAs('client', 'talk.wav');
```

For singing synthesis, create a Score first. `length` is a **frame length** value, and `Note::len($ticks, $bpm)` helps MIDI-oriented workflows.

```php
use Revolution\Voicevox\Song\Note;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\Voicevox;

$score = Score::make([
    Note::make(length: 15), // first note must be a rest
    Note::make(length: Note::len(ticks: 480, bpm: 120), lyric: 'ド', key: 60), // quarter note
    Note::make(length: Note::len(480, 120), lyric: 'レ', key: 62), // quarter note
    Note::make(length: Note::len(960, 120), lyric: 'ミ', key: 64), // half note
    Note::make(length: 2), // optional short tail silence
]);

$response = Voicevox::song($score, teacher: 6000)->generate(id: 3001);

$response->storeAs('client', 'song.wav');
```

### Native mode

Use native mode through `talk()` / `song()` helper functions.
Other than removing `Voicevox::`, usage is kept close to client mode.

Text-to-speech in native mode. Native mode does not provide `enable_katakana_english`, so for English text you may preprocess it (for example, convert to katakana with AI/LLM before synthesis).
`TalkAudioQuery` has the same class name but is a different class from the client one.

```php
use Revolution\Voicevox\Talk\TalkAudioQuery;
use function Revolution\Voicevox\talk;

$response = talk('ララベルが好きなのだ', id: 1)
    ->tap(function (TalkAudioQuery $talk): void {
        $talk->audioQuery['speedScale'] = 1.2;
    })->generate(id: 1);

$response->storeAs('native', 'talk.wav');
```

Singing synthesis in native mode. `Score` and `Note` are shared with client mode.

```php
use Revolution\Voicevox\Song\Note;
use Revolution\Voicevox\Song\Score;
use function Revolution\Voicevox\song;

$score = Score::make([
    Note::make(length: 15), // first note must be a rest
    Note::make(length: Note::len(ticks: 480, bpm: 120), lyric: 'ド', key: 60),
    Note::make(length: Note::len(480, 120), lyric: 'レ', key: 62),
    Note::make(length: Note::len(960, 120), lyric: 'ミ', key: 64),
    Note::make(length: 2), // optional short tail silence
]);

$response = song($score, teacher: 6000)->generate(id: 3001);

$response->storeAs('native', 'song.wav');
```

## Documentation

- [Japanese documentation](https://kawax.biz/jp/packages/laravel-voicevox)

## Terms of Use

You must follow the terms of use for VOICEVOX and each voice library.

## License

MIT
