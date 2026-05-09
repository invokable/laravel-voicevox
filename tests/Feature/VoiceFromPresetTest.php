<?php

declare(strict_types=1);

use Revolution\Voicevox\Client\VoiceAudioQuery;
use Revolution\Voicevox\Voicevox;

test('voice from preset returns VoiceAudioQuery', function () {
    Voicevox::expects('voiceFromPreset')->andReturn(new VoiceAudioQuery([]));

    $query = Voicevox::voiceFromPreset('テスト', preset_id: 1);

    expect($query)->toBeInstanceOf(VoiceAudioQuery::class);
});
