<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Client\TalkAudioQuery;
use Revolution\Voicevox\VoicevoxResponse;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('generate returns VoicevoxResponse', function () {
    Http::fake([
        'http://127.0.0.1:50021/synthesis*' => Http::response('wav-data'),
    ]);

    $response = (new TalkAudioQuery(['text' => 'テスト'], id: 1))->generate(id: 1);

    expect($response)->toBeInstanceOf(VoicevoxResponse::class)
        ->and($response->content())->toBe('wav-data');
});

test('generate uses configured core version', function () {
    config()->set('voicevox.client.core_version', '0.15.0');

    Http::fake([
        'http://127.0.0.1:50021/synthesis*' => Http::response('wav-data'),
    ]);

    (new TalkAudioQuery(['text' => 'テスト'], id: 1))->generate(id: 1);

    Http::assertSent(fn ($request) => str($request->url())->contains('core_version=0.15.0'));
});
