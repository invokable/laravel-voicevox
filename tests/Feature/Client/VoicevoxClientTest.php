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

    $query = app(VoicevoxClient::class)->talk('テスト', id: 1);

    expect($query)->toBeInstanceOf(TalkAudioQuery::class)
        ->and($query->audio_query)->toBe(['speedScale' => 1]);
});
