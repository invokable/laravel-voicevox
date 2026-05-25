<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Mockery\MockInterface;
use Revolution\Voicevox\Client\VoicevoxClient;
use Revolution\Voicevox\Engine\Http\ResourcesController;
use Revolution\Voicevox\Enums\Engine;
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

test('engine OpenAI speech endpoint returns wav using OpenAI voice name', function () {
    Synthesizer::expects('createAudioQuery')
        ->with('Hello from OpenAI compatible TTS', 3)
        ->andReturn(json_encode(['speedScale' => 1.0, 'accent_phrases' => []]));

    Synthesizer::expects('synthesis')
        ->withArgs(function (string $audioQuery, int $speaker, bool $enableInterrogativeUpspeak): bool {
            return data_get(json_decode($audioQuery, true), 'speedScale') === 1.5
                && $speaker === 3
                && $enableInterrogativeUpspeak;
        })
        ->andReturn('wav_binary_data');

    $response = $this->postJson('/v1/audio/speech', [
        'model' => 'tts-1',
        'input' => 'Hello from OpenAI compatible TTS',
        'voice' => 'alloy',
        'speed' => 1.5,
    ]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'audio/wav')
        ->assertContent('wav_binary_data');
});

test('engine OpenAI speech endpoint falls back to client and applies speed', function () {
    Synthesizer::expects('createAudioQuery')->andThrow(Exception::class);

    $this->mock(VoicevoxClient::class, function (MockInterface $mock) {
        $mock->expects('baseUrl')->andReturnSelf();
        $mock->expects('audioQuery')
            ->with('Fallback speech', 12)
            ->andReturn(['speedScale' => 1.0, 'accent_phrases' => []]);
        $mock->expects('synthesis')
            ->withArgs(function (array $audioQuery, int $speaker): bool {
                return data_get($audioQuery, 'speedScale') === 0.75
                    && $speaker === 12;
            })
            ->andReturn('fallback_wav_binary_data');
    });

    $response = $this->postJson('/v1/audio/speech', [
        'model' => 'tts-1',
        'input' => 'Fallback speech',
        'voice' => 'onyx',
        'speed' => 0.75,
    ]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'audio/wav')
        ->assertContent('fallback_wav_binary_data');
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
