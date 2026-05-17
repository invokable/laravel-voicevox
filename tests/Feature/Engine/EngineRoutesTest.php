<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Revolution\Voicevox\Engine\Engine;
use Revolution\Voicevox\Engine\Http\ResourcesController;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;

test('engine audio_query endpoint returns json', function () {
    Synthesizer::expects('createAudioQuery')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->audioQuery')->andReturn(['speedScale' => 1.0]);

    $response = $this->postJson('/audio_query?text=テスト&speaker=1');

    $response->assertOk()
        ->assertJsonFragment(['speedScale' => 1.0]);
});

test('engine synthesis endpoint returns wav', function () {
    Synthesizer::expects('synthesis')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->synthesis')->andReturn('wav_binary_data');

    $response = $this->postJson('/synthesis?speaker=1', ['speedScale' => 1.0]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'audio/wav');
});

test('engine speakers endpoint returns array', function () {
    Synthesizer::expects('metas')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->speakers')->andReturn([['name' => 'ずんだもん']]);

    $response = $this->getJson('/speakers');

    $response->assertOk()
        ->assertJsonFragment(['name' => 'ずんだもん']);
});

test('engine speaker_info endpoint returns info', function () {
    Synthesizer::expects('metas')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->speaker')->andReturn(['policy' => 'test']);

    $response = $this->getJson('/speaker_info?speaker_uuid=388f246b-8c41-4ac1-8e2d-5d79f3ff56d9');

    $response->assertOk()
        ->assertJsonFragment(['policy' => 'test']);
});

test('engine singers endpoint returns array', function () {
    Synthesizer::expects('metas')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->singers')->andReturn([['name' => 'ずんだもん']]);

    $response = $this->getJson('/singers');

    $response->assertOk();
});

test('engine singer_info endpoint returns info', function () {
    Synthesizer::expects('metas')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->singer')->andReturn(['policy' => 'test']);

    $response = $this->getJson('/singer_info?speaker_uuid=388f246b-8c41-4ac1-8e2d-5d79f3ff56d9');

    $response->assertOk();
});

test('engine version endpoint returns version', function () {
    $response = $this->getJson('/version');

    $response->assertOk()
        ->assertSee(Engine::Version->value);
});

test('engine resources endpoint returns 404 for unknown hash', function () {
    $response = $this->get('/_resources/'.str_repeat('0', 64));

    $response->assertNotFound();
});

test('engine resources endpoint returns file for known hash', function () {
    $tempDir = sys_get_temp_dir().'/resources_test_'.uniqid();
    File::makeDirectory($tempDir);
    $content = 'fake-png-content';
    File::put($tempDir.'/icon.png', $content);
    $hash = hash('sha256', $content);

    $this->app->bind(ResourcesController::class, fn () => new ResourcesController($tempDir));

    $response = $this->get("/_resources/{$hash}");

    $response->assertOk();

    File::deleteDirectory($tempDir);
});
