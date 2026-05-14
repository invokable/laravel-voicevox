<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Laravel\Ai\Audio;
use Laravel\Ai\Responses\AudioResponse;

beforeEach(function () {
    Http::preventStrayRequests();

    config([
        'ai.providers.voicevox' => [
            'driver' => 'voicevox',
            'key' => 'http://127.0.0.1:50021',
        ],
    ]);
});

test('voicevox ai provider generates audio', function () {
    Http::fake([
        'http://127.0.0.1:50021/audio_query*' => Http::response(['speedScale' => 1.0]),
        'http://127.0.0.1:50021/synthesis*' => Http::response('fake-wav-bytes'),
    ]);

    $response = Audio::of('ララベルが好きなのだ')->voice('1')->generate('voicevox');

    expect($response)->toBeInstanceOf(AudioResponse::class)
        ->and($response->mimeType())->toBe('audio/wav')
        ->and($response->content())->toBe('fake-wav-bytes');
});

test('voicevox ai provider uses default-female voice', function () {
    Http::fake([
        'http://127.0.0.1:50021/audio_query*' => Http::response(['speedScale' => 1.0]),
        'http://127.0.0.1:50021/synthesis*' => Http::response('fake-wav-bytes'),
    ]);

    $response = Audio::of('テスト')->generate('voicevox');

    Http::assertSent(fn ($req) => str_contains($req->url(), 'speaker=1'));
});
