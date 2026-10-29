<?php

declare(strict_types=1);

use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;

test('engine streaming_synthesis falls back to the engine', function () {
    Synthesizer::expects('createAudioFeature')->andThrow(new Exception);
    Voicevox::expects('baseUrl->streamingSynthesis')
        ->with(['speedScale' => 1.0], 2, 0.0, 0.3, true)
        ->andReturn('wav_binary_data');

    $response = $this->postJson('/streaming_synthesis?speaker=2', ['speedScale' => 1.0]);

    $response->assertOk()->assertHeader('Content-Type', 'audio/wav');
    expect($response->getContent())->toBe('wav_binary_data');
});

test('engine streaming_synthesis passes offset and segment length to the fallback', function () {
    Synthesizer::expects('createAudioFeature')->andThrow(new Exception);
    Voicevox::expects('baseUrl->streamingSynthesis')
        ->with(['speedScale' => 1.0], 1, 1.5, 0.5, false)
        ->andReturn('wav_binary_data');

    $response = $this->postJson(
        '/streaming_synthesis?speaker=1&start_offset=1.5&segment_length=0.5&enable_interrogative_upspeak=false',
        ['speedScale' => 1.0],
    );

    $response->assertOk();
});

test('engine streaming_synthesis returns 501 when native and fallback fail', function () {
    Synthesizer::expects('createAudioFeature')->andThrow(new Exception);
    Voicevox::expects('baseUrl->streamingSynthesis')->andThrow(new Exception);

    $response = $this->postJson('/streaming_synthesis?speaker=1', ['speedScale' => 1.0]);

    $response->assertStatus(501)->assertJsonStructure(['error']);
});

test('engine streaming_synthesis rejects a negative start_offset', function () {
    Synthesizer::shouldReceive('createAudioFeature')->never();

    $response = $this->postJson('/streaming_synthesis?speaker=1&start_offset=-1', ['speedScale' => 1.0]);

    $response->assertStatus(422)->assertJsonValidationErrors('start_offset');
});

test('engine streaming_synthesis rejects a non positive segment_length', function () {
    Synthesizer::shouldReceive('createAudioFeature')->never();

    $response = $this->postJson('/streaming_synthesis?speaker=1&segment_length=0', ['speedScale' => 1.0]);

    $response->assertStatus(422)->assertJsonValidationErrors('segment_length');
});
