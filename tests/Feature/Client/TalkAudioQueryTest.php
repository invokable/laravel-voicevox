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
