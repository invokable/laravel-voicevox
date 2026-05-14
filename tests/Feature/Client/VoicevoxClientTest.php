<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Client\TalkAudioQuery;
use Revolution\Voicevox\Client\VoicevoxClient;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('talk returns TalkAudioQuery', function () {
    Http::fake([
        'http://127.0.0.1:50021/audio_query*' => Http::response(['speedScale' => 1]),
    ]);

    $query = app(VoicevoxClient::class)->talk('テスト', id: 1, enableKatakanaEnglish: false);

    Http::assertSent(fn ($request) => str($request->url())->contains('enable_katakana_english=0'));

    expect($query)->toBeInstanceOf(TalkAudioQuery::class)
        ->and($query->audioQuery)->toBe(['speedScale' => 1]);
});

test('talk uses configured core version', function () {
    config()->set('voicevox.client.core_version', '0.15.0');

    Http::fake([
        'http://127.0.0.1:50021/audio_query*' => Http::response(['speedScale' => 1]),
    ]);

    app(VoicevoxClient::class)->talk('テスト');

    Http::assertSent(fn ($request) => str($request->url())->contains('core_version=0.15.0'));
});
