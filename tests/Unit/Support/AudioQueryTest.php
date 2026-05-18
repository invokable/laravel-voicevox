<?php

declare(strict_types=1);

use Revolution\Voicevox\Talk\AudioQuery;

test('audio query', function () {
    $audio_query = new AudioQuery(kana: 'カナ');
    expect($audio_query->toArray())->toBe([
        'accent_phrases' => [],
        'speedScale' => 1.0,
        'pitchScale' => 0.0,
        'intonationScale' => 1.0,
        'volumeScale' => 1.0,
        'prePhonemeLength' => 0.1,
        'postPhonemeLength' => 0.1,
        'outputSamplingRate' => 24000,
        'outputStereo' => false,
        'kana' => 'カナ',
    ]);
});
