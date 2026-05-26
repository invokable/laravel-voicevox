<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $corePath = rtrim((string) config('voicevox.core.path', ''), '/');
    $coreLibPath = $corePath.'/c_api/lib/libvoicevox_core.so';

    if ($corePath === '' || ! File::exists($coreLibPath)) {
        $this->markTestSkipped('VOICEVOX core runtime is not configured.');
    }
});

test('engine sing_frame_f0 endpoint adjusts f0 for frame audio query', function () {
    $score = [
        'notes' => [
            ['frame_length' => 10, 'lyric' => '', 'key' => null],
            ['frame_length' => 100, 'lyric' => 'あ', 'key' => 60],
            ['frame_length' => 100, 'lyric' => 'い', 'key' => 62],
        ],
    ];

    $frameAudioQuery = $this->postJson('/sing_frame_audio_query?speaker=6000', $score)
        ->assertOk()
        ->json();

    expect($frameAudioQuery)->toBeArray()
        ->toHaveKey('f0')
        ->toHaveKey('phonemes');

    // Apply sing_frame_f0
    $response = $this->postJson('/sing_frame_f0?speaker=6000', [
        'score' => $score,
        'frame_audio_query' => $frameAudioQuery,
    ])
        ->assertOk()
        ->json();

    expect($response)->toBeArray()
        ->toHaveKey('f0')
        ->and($response['f0'])->toBeArray()->not->toBeEmpty();
});

test('engine sing_frame_volume endpoint adjusts volume for frame audio query', function () {
    $score = [
        'notes' => [
            ['frame_length' => 10, 'lyric' => '', 'key' => null],
            ['frame_length' => 100, 'lyric' => 'う', 'key' => 64],
        ],
    ];

    $frameAudioQuery = $this->postJson('/sing_frame_audio_query?speaker=6000', $score)
        ->assertOk()
        ->json();

    expect($frameAudioQuery)->toBeArray()
        ->toHaveKey('volume');

    // Apply sing_frame_volume
    $response = $this->postJson('/sing_frame_volume?speaker=6000', [
        'score' => $score,
        'frame_audio_query' => $frameAudioQuery,
    ])
        ->assertOk()
        ->json();

    expect($response)->toBeArray()
        ->toHaveKey('volume')
        ->and($response['volume'])->toBeArray()->not->toBeEmpty();
});

test('engine singers endpoint returns singer metadata', function () {
    $response = $this->getJson('/singers')
        ->assertOk()
        ->json();

    expect($response)->toBeArray()->not->toBeEmpty();
});

test('engine singer_info endpoint returns detailed singer info', function () {
    $response = $this->getJson('/singer_info?speaker_uuid=7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff');

    // singer_info requires character_info resources which may not be installed
    if ($response->status() === 501) {
        $this->markTestSkipped('singer_info requires character_info resources.');
    }

    $response->assertOk();
    $data = $response->json();

    expect($data)->toBeArray()
        ->and($data['policy'] ?? null)->toBeString();
});

test('complete sing workflow with f0 and volume adjustment', function () {
    $score = [
        'notes' => [
            ['frame_length' => 10, 'lyric' => '', 'key' => null],
            ['frame_length' => 100, 'lyric' => 'え', 'key' => 65],
            ['frame_length' => 100, 'lyric' => 'お', 'key' => 67],
        ],
    ];

    // Step 1: Create frame audio query
    $frameAudioQuery = $this->postJson('/sing_frame_audio_query?speaker=6000', $score)
        ->assertOk()
        ->json();

    expect($frameAudioQuery)->toBeArray()
        ->toHaveKey('f0')
        ->toHaveKey('volume');

    // Step 2: Adjust f0
    $adjustedF0 = $this->postJson('/sing_frame_f0?speaker=6000', [
        'score' => $score,
        'frame_audio_query' => $frameAudioQuery,
    ])
        ->assertOk()
        ->json();

    expect($adjustedF0['f0'])->toBeArray()->not->toBeEmpty();

    // Step 3: Adjust volume
    $adjustedVolume = $this->postJson('/sing_frame_volume?speaker=6000', [
        'score' => $score,
        'frame_audio_query' => $adjustedF0,
    ])
        ->assertOk()
        ->json();

    expect($adjustedVolume['volume'])->toBeArray()->not->toBeEmpty();

    // Step 4: Synthesize with adjusted parameters
    $response = $this->postJson('/frame_synthesis?speaker=6000', $adjustedVolume)
        ->assertOk()
        ->assertHeader('Content-Type', 'audio/wav');

    expect($response->getContent())->toStartWith('RIFF');
});
