<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('user dict returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/user_dict' => Http::response(['uuid-1' => ['surface' => 'Laravel']]),
    ]);

    $dict = Voicevox::userDict();

    expect($dict)->toBeArray()->toHaveKey('uuid-1');
});

test('add word returns uuid', function () {
    $uuid = 'a1b2c3d4-0000-0000-0000-000000000000';

    Http::fake([
        'http://127.0.0.1:50021/user_dict_word*' => Http::response(json_encode($uuid, JSON_THROW_ON_ERROR), 200, ['Content-Type' => 'application/json']),
    ]);

    $result = Voicevox::addWord('Laravel', 'ララベル', 3);

    expect($result)->toBe($uuid);
});

test('update word', function () {
    Http::fake([
        'http://127.0.0.1:50021/user_dict_word/*' => Http::response(),
    ]);

    Voicevox::updateWord('uuid-1', 'Laravel', 'ララベル', 3);

    Http::assertSentCount(1);
});

test('delete word', function () {
    Http::fake([
        'http://127.0.0.1:50021/user_dict_word/*' => Http::response(),
    ]);

    Voicevox::deleteWord('uuid-1');

    Http::assertSentCount(1);
});
