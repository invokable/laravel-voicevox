<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('accentPhrases returns array', function () {
    $phrases = [['moras' => [], 'accent' => 1]];

    Http::fake([
        'http://127.0.0.1:50021/accent_phrases*' => Http::response($phrases),
    ]);

    $result = Voicevox::accentPhrases('こんにちは', 1);

    expect($result)->toBeArray()->toHaveCount(1);
});

test('moraData returns array', function () {
    $phrases = [['moras' => [], 'accent' => 1]];

    Http::fake([
        'http://127.0.0.1:50021/mora_data*' => Http::response($phrases),
    ]);

    $result = Voicevox::moraData($phrases, 1);

    expect($result)->toBeArray();
});

test('moraLength returns array', function () {
    $phrases = [['moras' => [], 'accent' => 1]];

    Http::fake([
        'http://127.0.0.1:50021/mora_length*' => Http::response($phrases),
    ]);

    $result = Voicevox::moraLength($phrases, 1);

    expect($result)->toBeArray();
});

test('moraPitch returns array', function () {
    $phrases = [['moras' => [], 'accent' => 1]];

    Http::fake([
        'http://127.0.0.1:50021/mora_pitch*' => Http::response($phrases),
    ]);

    $result = Voicevox::moraPitch($phrases, 1);

    expect($result)->toBeArray();
});
