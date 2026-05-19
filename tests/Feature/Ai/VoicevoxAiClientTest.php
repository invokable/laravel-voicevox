<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Laravel\Ai\Audio;
use Laravel\Ai\Responses\AudioResponse;

beforeEach(function () {
    Http::preventStrayRequests();

    config([
        'ai.providers.voicevox-client' => [
            'driver' => 'voicevox-client',
            'key' => 'http://127.0.0.1:50021',
        ],
    ]);
});

test('voicevox ai provider generates audio', function () {
    Http::fake([
        'http://127.0.0.1:50021/audio_query*' => Http::response(['speedScale' => 1.0]),
        'http://127.0.0.1:50021/synthesis*' => Http::response('fake-wav-bytes'),
    ]);

    $response = Audio::of('ララベルが好きなのだ')->voice('ずんだもん')->generate('voicevox-client');

    expect($response)->toBeInstanceOf(AudioResponse::class)
        ->and($response->mimeType())->toBe('audio/wav')
        ->and($response->content())->toBe('fake-wav-bytes');
});

test('voicevox ai provider uses default-female voice', function () {
    Http::fake([
        'http://127.0.0.1:50021/audio_query*' => Http::response(['speedScale' => 1.0]),
        'http://127.0.0.1:50021/synthesis*' => Http::response('fake-wav-bytes'),
    ]);

    $response = Audio::of('テスト')->generate('voicevox-client');

    Http::assertSent(fn ($req) => str_contains($req->url(), 'speaker=10'));
});

test('voicevox ai provider respects configured engine url', function () {
    config([
        'ai.providers.voicevox-custom' => [
            'driver' => 'voicevox-client',
            'key' => 'http://192.168.1.100:50021',
        ],
    ]);

    Http::fake([
        'http://192.168.1.100:50021/audio_query*' => Http::response(['speedScale' => 1.0]),
        'http://192.168.1.100:50021/synthesis*' => Http::response('custom-wav-bytes'),
    ]);

    $response = Audio::of('カスタムURLテスト')->voice('ずんだもん')->generate('voicevox-custom');

    expect($response->content())->toBe('custom-wav-bytes');
    Http::assertSent(fn ($req) => str_starts_with($req->url(), 'http://192.168.1.100:50021'));
});

test('voicevox ai provider resolves named voice aliases', function () {
    Http::fake([
        'http://127.0.0.1:50021/audio_query*' => Http::response(['speedScale' => 1.0]),
        'http://127.0.0.1:50021/synthesis*' => Http::response('fake-wav-bytes'),
    ]);

    Audio::of('テスト')->voice('四国めたん')->generate('voicevox-client');

    Http::assertSent(fn ($req) => str_contains($req->url(), 'speaker=2'));
});
