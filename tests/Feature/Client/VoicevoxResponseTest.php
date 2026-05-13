<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Revolution\Voicevox\VoicevoxResponse;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('content returns body', function () {
    $response = new VoicevoxResponse('wav-data');

    expect($response->content())->toBe('wav-data');
});

test('__toString returns body', function () {
    $response = new VoicevoxResponse('wav-data');

    expect((string) $response)->toBe('wav-data');
});

test('storeAs stores wav file', function () {
    Storage::fake('local');

    $response = new VoicevoxResponse('wav-data');
    $path = $response->storeAs('voices', 'test.wav', 'local');

    expect($path)->toBe('voices/test.wav');
    Storage::disk('local')->assertExists('voices/test.wav');
});
