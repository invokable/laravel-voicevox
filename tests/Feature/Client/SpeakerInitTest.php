<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Client\TalkResponse;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('initializeSpeaker can be called', function () {
    Http::fake([
        'http://127.0.0.1:50021/initialize_speaker*' => Http::response(),
    ]);

    Voicevox::initializeSpeaker(1);

    Http::assertSentCount(1);
});

test('isInitializedSpeaker returns bool', function () {
    Http::fake([
        'http://127.0.0.1:50021/is_initialized_speaker*' => Http::response('true', 200, ['Content-Type' => 'application/json']),
    ]);

    $result = Voicevox::isInitializedSpeaker(1);

    expect($result)->toBeTrue();
});

test('validateKana returns bool', function () {
    Http::fake([
        'http://127.0.0.1:50021/validate_kana*' => Http::response('true', 200, ['Content-Type' => 'application/json']),
    ]);

    $result = Voicevox::validateKana("コンニチワ'");

    expect($result)->toBeTrue();
});

test('multiSynthesis returns TalkResponse', function () {
    Http::fake([
        'http://127.0.0.1:50021/multi_synthesis*' => Http::response('wav-data'),
    ]);

    $result = Voicevox::multiSynthesis([[], []], 1);

    expect($result)->toBeInstanceOf(TalkResponse::class);
});
