<?php

declare(strict_types=1);

use Revolution\Voicevox\Client\TalkResponse;
use Revolution\Voicevox\Voicevox;

test('morphableTargets returns array', function () {
    Voicevox::expects('morphableTargets')->andReturn([['1' => ['is_morphable' => true]]]);

    $targets = Voicevox::morphableTargets([1]);

    expect($targets)->toBeArray()->toHaveCount(1);
});

test('morphing returns VoiceResponse', function () {
    Voicevox::expects('morphing')->andReturn(new TalkResponse('wav-data'));

    $response = Voicevox::morphing(audio_query: [], base_speaker: 1, target_speaker: 3, morph_rate: 0.5);

    expect($response)->toBeInstanceOf(TalkResponse::class);
    expect($response->content())->toBe('wav-data');
});

test('connectWaves returns VoiceResponse', function () {
    Voicevox::expects('connectWaves')->andReturn(new TalkResponse('combined-wav'));

    $response = Voicevox::connectWaves(['base64wav1', 'base64wav2']);

    expect($response)->toBeInstanceOf(TalkResponse::class);
    expect($response->content())->toBe('combined-wav');
});
