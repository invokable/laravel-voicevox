<?php

declare(strict_types=1);

use Revolution\Voicevox\Song\Song;
use Revolution\Voicevox\Song\SongAudioQuery;

test('song function returns SongAudioQuery via facade', function () {
    expect(function_exists('Revolution\Voicevox\song'))->toBeTrue();
});

test('Song::make is callable', function () {
    expect(method_exists(Song::class, 'make'))->toBeTrue();
});

test('SongAudioQuery accepts array and id', function () {
    $query = new SongAudioQuery(['f0' => [], 'volume' => [], 'phonemes' => []], id: 6000);

    expect($query->frameAudioQuery)->toHaveKey('f0')
        ->and($query->id)->toBe(6000);
});

test('SongAudioQuery is tappable', function () {
    $query = new SongAudioQuery(['f0' => [0.0], 'volume' => [], 'phonemes' => []], id: 6000);

    $tapped = $query->tap(function (SongAudioQuery $q) {
        $q->frameAudioQuery['f0'] = [440.0];
    });

    expect($tapped)->toBe($query)
        ->and($query->frameAudioQuery['f0'])->toBe([440.0]);
});
