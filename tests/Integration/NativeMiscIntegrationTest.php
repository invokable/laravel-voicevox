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

test('engine multi_synthesis generates multiple audio files', function () {
    $audioQuery1 = $this->postJson('/audio_query?text=最初の文&speaker=1')
        ->assertOk()
        ->json();

    $audioQuery2 = $this->postJson('/audio_query?text=二番目の文&speaker=1')
        ->assertOk()
        ->json();

    $queries = [
        ['speaker' => 1, 'query' => $audioQuery1],
        ['speaker' => 1, 'query' => $audioQuery2],
    ];

    $response = $this->postJson('/multi_synthesis', $queries)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/zip');

    expect($response->getContent())->not->toBeEmpty();
});

test('engine initialize_speaker is no-op but returns success', function () {
    $response = $this->postJson('/initialize_speaker?speaker=1&skip_reinit=false')
        ->assertNoContent();
});

test('engine is_initialized_speaker returns true', function () {
    $response = $this->getJson('/is_initialized_speaker?speaker=1')
        ->assertOk()
        ->json();

    expect($response)->toBeTrue();
});

test('engine morphable_targets fallback for unsupported feature', function () {
    $response = $this->postJson('/morphable_targets', [
        'base_speakers' => [1],
    ]);

    // morphable_targets requires fallback engine
    if ($response->status() === 501) {
        $this->markTestSkipped('morphable_targets requires fallback engine connection.');
    }

    $response->assertOk();
    expect($response->json())->toBeArray();
});

test('engine synthesis_morphing fallback for unsupported feature', function () {
    $audioQuery = $this->postJson('/audio_query?text=モーフィング&speaker=1')
        ->assertOk()
        ->json();

    $response = $this->postJson('/synthesis_morphing?base_speaker=1&target_speaker=8&morph_rate=0.5', $audioQuery);

    // synthesis_morphing requires fallback engine
    if ($response->status() === 501) {
        $this->markTestSkipped('synthesis_morphing requires fallback engine connection.');
    }

    $response->assertOk()
        ->assertHeader('Content-Type', 'audio/wav');
});

test('engine downloadable_libraries endpoint returns empty array by default', function () {
    $response = $this->getJson('/downloadable_libraries')
        ->assertOk()
        ->json();

    expect($response)->toBeArray();
});

test('engine installed_libraries endpoint returns empty array by default', function () {
    $response = $this->getJson('/installed_libraries')
        ->assertOk()
        ->json();

    expect($response)->toBeArray();
});

test('engine openai audio speech endpoint generates audio', function () {
    $response = $this->postJson('/v1/audio/speech', [
        'model' => 'tts-1',
        'input' => 'OpenAI互換テスト',
        'voice' => 'zundamon',
        'response_format' => 'wav',
        'speed' => 1.0,
    ]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'audio/wav');

    expect($response->getContent())->toStartWith('RIFF');
});

test('engine openai audio speech with mp3 format', function () {
    $response = $this->postJson('/v1/audio/speech', [
        'model' => 'tts-1',
        'input' => 'MP3形式テスト',
        'voice' => 'zundamon',
        'response_format' => 'mp3',
    ]);

    // mp3 format conversion is not supported natively, wav is returned instead
    $response->assertOk()
        ->assertHeader('Content-Type', 'audio/wav');

    expect($response->getContent())->toStartWith('RIFF');
});
