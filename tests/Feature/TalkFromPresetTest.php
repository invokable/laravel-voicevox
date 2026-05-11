<?php

declare(strict_types=1);

use Revolution\Voicevox\Client\TalkAudioQuery;
use Revolution\Voicevox\Voicevox;

test('voice from preset returns VoiceAudioQuery', function () {
    Voicevox::expects('talkFromPreset')->andReturn(new TalkAudioQuery([]));

    $query = Voicevox::talkFromPreset('テスト', preset_id: 1);

    expect($query)->toBeInstanceOf(TalkAudioQuery::class);
});
