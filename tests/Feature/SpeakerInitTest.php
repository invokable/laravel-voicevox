<?php

declare(strict_types=1);

use Revolution\Voicevox\Voicevox;

test('initializeSpeaker can be called', function () {
    Voicevox::expects('initializeSpeaker')->andReturnNull();

    Voicevox::initializeSpeaker(1);

    expect(true)->toBeTrue();
});

test('isInitializedSpeaker returns bool', function () {
    Voicevox::expects('isInitializedSpeaker')->andReturn(true);

    $result = Voicevox::isInitializedSpeaker(1);

    expect($result)->toBeTrue();
});

test('validateKana returns bool', function () {
    Voicevox::expects('validateKana')->andReturn(true);

    $result = Voicevox::validateKana("コンニチワ'");

    expect($result)->toBeTrue();
});

test('multiSynthesis returns TalkResponse', function () {
    $mock = Mockery::mock(\Revolution\Voicevox\Client\TalkResponse::class);
    Voicevox::expects('multiSynthesis')->andReturn($mock);

    $result = Voicevox::multiSynthesis([[], []], 1);

    expect($result)->toBeInstanceOf(\Revolution\Voicevox\Client\TalkResponse::class);
});
