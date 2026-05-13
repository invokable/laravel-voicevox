<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\VoicevoxResponse;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('morphableTargets returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/morphable_targets*' => Http::response([['1' => ['is_morphable' => true]]]),
    ]);

    $targets = Voicevox::morphableTargets([1]);

    expect($targets)->toBeArray()->toHaveCount(1);
});

test('morphing returns VoicevoxResponse', function () {
    Http::fake([
        'http://127.0.0.1:50021/synthesis_morphing*' => Http::response('wav-data'),
    ]);

    $response = Voicevox::morphing(audio_query: [], base_speaker: 1, target_speaker: 3, morph_rate: 0.5);

    expect($response)->toBeInstanceOf(VoicevoxResponse::class);
    expect($response->content())->toBe('wav-data');
});

test('connectWaves returns VoicevoxResponse', function () {
    Http::fake([
        'http://127.0.0.1:50021/connect_waves' => Http::response('combined-wav'),
    ]);

    $response = Voicevox::connectWaves(['base64wav1', 'base64wav2']);

    expect($response)->toBeInstanceOf(VoicevoxResponse::class);
    expect($response->content())->toBe('combined-wav');
});
