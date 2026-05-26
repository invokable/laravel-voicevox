<?php

declare(strict_types=1);

use Revolution\Voicevox\Core\VoicevoxCore;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\VoicevoxResponse;

test('engine cancellable_synthesis returns audio', function () {
    Voicevox::expects('baseUrl->cancellableSynthesis')->andReturn('audio-bytes');

    $response = $this->postJson('/cancellable_synthesis?speaker=1', ['speedScale' => 1.0]);

    $response->assertOk();
});

test('engine multi_synthesis returns audio', function () {
    Synthesizer::expects('synthesis')->twice()->andReturn('audio-bytes');

    $response = $this->postJson('/multi_synthesis?speaker=1', [['speedScale' => 1.0], ['speedScale' => 1.0]]);

    $response->assertOk();
});

test('engine connect_waves returns audio', function () {
    $mock = Mockery::mock(VoicevoxResponse::class);
    $mock->shouldReceive('content')->andReturn('audio-bytes');
    Voicevox::expects('baseUrl->connectWaves')->andReturn($mock);

    $response = $this->postJson('/connect_waves', []);

    $response->assertOk();
});

test('engine morphable_targets returns json', function () {
    Voicevox::expects('baseUrl->morphableTargets')->andReturn([['is_morphable' => true]]);

    $response = $this->postJson('/morphable_targets', [1, 2]);

    $response->assertOk()
        ->assertJsonFragment(['is_morphable' => true]);
});

test('engine synthesis_morphing returns audio', function () {
    $mock = Mockery::mock(VoicevoxResponse::class);
    $mock->shouldReceive('content')->andReturn('audio-bytes');
    Voicevox::expects('baseUrl->morphing')->andReturn($mock);

    $response = $this->postJson('/synthesis_morphing?base_speaker=1&target_speaker=2&morph_rate=0.5', ['speedScale' => 1.0]);

    $response->assertOk();
});

test('engine sing_frame_audio_query returns json', function () {
    $this->mock(VoicevoxCore::class, function ($mock) {
        $mock->allows('scoreValidate')->andReturn(null);
    });
    Synthesizer::expects('createSingFrameAudioQuery')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->singFrameAudioQuery')->andReturn(['f0' => [], 'volume' => []]);

    $response = $this->postJson('/sing_frame_audio_query?speaker=6000', ['notes' => []]);

    $response->assertOk()
        ->assertJsonStructure(['f0', 'volume']);
});

test('engine sing_frame_f0 uses core and returns f0 array', function () {
    Synthesizer::expects('createSingFrameF0')->andReturn(
        json_encode([0.0, 440.0]),
    );

    $response = $this->postJson('/sing_frame_f0?speaker=6000', [
        'score' => ['notes' => []],
        'frame_audio_query' => ['f0' => [], 'volume' => [], 'phonemes' => []],
    ]);

    $response->assertOk()
        ->assertJson([0.0, 440.0]);
});

test('engine sing_frame_f0 falls back to client when core throws', function () {
    Synthesizer::expects('createSingFrameF0')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->singFrameF0')->andReturn([0.0, 440.0]);

    $response = $this->postJson('/sing_frame_f0?speaker=6000', [
        'score' => ['notes' => []],
        'frame_audio_query' => ['f0' => [], 'volume' => [], 'phonemes' => []],
    ]);

    $response->assertOk()
        ->assertJson([0.0, 440.0]);
});

test('engine sing_frame_volume uses core and returns volume array', function () {
    Synthesizer::expects('createSingFrameVolume')->andReturn(
        json_encode([0.0, 1.0]),
    );

    $response = $this->postJson('/sing_frame_volume?speaker=6000', [
        'score' => ['notes' => []],
        'frame_audio_query' => ['f0' => [], 'volume' => [], 'phonemes' => []],
    ]);

    $response->assertOk()
        ->assertJson([0.0, 1.0]);
});

test('engine sing_frame_volume falls back to client when core throws', function () {
    Synthesizer::expects('createSingFrameVolume')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->singFrameVolume')->andReturn([0.0, 1.0]);

    $response = $this->postJson('/sing_frame_volume?speaker=6000', [
        'score' => ['notes' => []],
        'frame_audio_query' => ['f0' => [], 'volume' => [], 'phonemes' => []],
    ]);

    $response->assertOk()
        ->assertJson([0.0, 1.0]);
});

test('engine frame_synthesis returns audio', function () {
    Synthesizer::expects('frameSynthesis')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->frameSynthesis')->andReturn('audio-bytes');

    $response = $this->postJson('/frame_synthesis?speaker=3001', ['f0' => [], 'volume' => []]);

    $response->assertOk();
});

test('engine core_versions returns array', function () {
    $this->mock(VoicevoxCore::class, function ($mock) {
        $mock->allows('getVersion')->andReturn('0.16.0');
    });

    $response = $this->getJson('/core_versions');

    $response->assertOk()
        ->assertJsonFragment(['0.16.0']);
});

test('engine supported_devices returns json', function () {
    Voicevox::expects('baseUrl->supportedDevices')->andReturn(['cpu' => true]);

    $response = $this->getJson('/supported_devices');

    $response->assertOk()
        ->assertJsonFragment(['cpu' => true]);
});

test('engine initialize_speaker returns 204', function () {
    $response = $this->postJson('/initialize_speaker?speaker=1');

    $response->assertNoContent();
});

test('engine is_initialized_speaker returns bool', function () {
    $response = $this->getJson('/is_initialized_speaker?speaker=1');

    $response->assertOk()
        ->assertSee('true');
});

test('engine downloadable_libraries returns json', function () {
    $response = $this->getJson('/downloadable_libraries');

    $response->assertOk()
        ->assertJson([]);
});

test('engine installed_libraries returns json', function () {
    $response = $this->getJson('/installed_libraries');

    $response->assertOk()
        ->assertJson([]);
});

test('engine install_library returns 204', function () {
    $response = $this->postJson('/install_library/some-uuid');

    $response->assertOk()
        ->assertJson([]);
});

test('engine uninstall_library returns 204', function () {
    $response = $this->postJson('/uninstall_library/some-uuid');

    $response->assertOk()
        ->assertJson([]);
});

test('engine GET setting returns json', function () {
    Voicevox::expects('baseUrl->setting')->andReturn('html');

    $response = $this->get('/setting');

    $response->assertOk()
        ->assertSee('html');
});

test('engine POST setting returns 204', function () {
    Voicevox::expects('baseUrl->updateSetting')->andReturn(null);

    $response = $this->postJson('/setting', ['allow_origin' => '*']);

    $response->assertNoContent();
});

test('engine alive route returns html', function () {
    Voicevox::expects('baseUrl->alive')->andReturn('<html>VOICEVOX</html>');

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('VOICEVOX');
});

test('engine cancellable_synthesis falls back to 501', function () {
    Voicevox::expects('baseUrl->cancellableSynthesis')->andThrow(Exception::class);

    $response = $this->postJson('/cancellable_synthesis?speaker=1', []);

    $response->assertStatus(501);
});
