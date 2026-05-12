<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Client\SongAudioQuery;
use Revolution\Voicevox\Client\SongResponse;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('singFrameAudioQuery returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/sing_frame_audio_query*' => Http::response(['f0' => [440.0], 'volume' => [1.0], 'phonemes' => []]),
    ]);

    $query = Voicevox::song(['notes' => []], 3001);

    expect($query->frame_audio_query)->toBeArray()->toHaveKey('f0');
});

test('singFrameAudioQuery accepts Arrayable Score', function () {
    Http::fake([
        'http://127.0.0.1:50021/sing_frame_audio_query*' => Http::response(['f0' => [], 'volume' => [], 'phonemes' => []]),
    ]);

    $score = new Score(notes: []);
    $query = Voicevox::song($score, 3001);

    expect($query)->toBeInstanceOf(SongAudioQuery::class);
});

test('singFrameF0 returns array of floats', function () {
    Http::fake([
        'http://127.0.0.1:50021/sing_frame_f0*' => Http::response([0.0, 440.0, 440.0]),
    ]);

    $f0 = Voicevox::singFrameF0(['notes' => []], ['f0' => [], 'volume' => [], 'phonemes' => []], 3001);

    expect($f0)->toBeArray();
});

test('singFrameVolume returns array of floats', function () {
    Http::fake([
        'http://127.0.0.1:50021/sing_frame_volume*' => Http::response([0.0, 1.0, 1.0]),
    ]);

    $vol = Voicevox::singFrameVolume(['notes' => []], ['f0' => [], 'volume' => [], 'phonemes' => []], 3001);

    expect($vol)->toBeArray();
});

test('frameSynthesis returns TalkResponse', function () {
    Http::fake([
        'http://127.0.0.1:50021/frame_synthesis*' => Http::response('binary-audio-data'),
    ]);

    $songAudioQuery = new SongAudioQuery(['notes' => []], ['f0' => [], 'volume' => [], 'phonemes' => []]);
    $response = $songAudioQuery->generate(id: 3001);

    expect($response)->toBeInstanceOf(SongResponse::class);
    expect($response->content())->toBe('binary-audio-data');
});
