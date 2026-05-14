<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Client\TalkAudioQuery;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('voice from preset returns VoiceAudioQuery', function () {
    Http::fake([
        'http://127.0.0.1:50021/audio_query_from_preset*' => Http::response([]),
    ]);

    $query = Voicevox::talkFromPreset('テスト', presetId: 1);

    expect($query)->toBeInstanceOf(TalkAudioQuery::class);
});
