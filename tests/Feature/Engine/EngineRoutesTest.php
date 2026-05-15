<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('engine audio_query endpoint returns json', function () {
    Http::fake([
        'http://127.0.0.1:50021/audio_query*' => Http::response(['speedScale' => 1.0]),
    ]);

    $response = $this->postJson('/audio_query?text=テスト&speaker=1');

    $response->assertOk()
        ->assertJsonFragment(['speedScale' => 1.0]);
});

test('engine synthesis endpoint returns wav', function () {
    Http::fake([
        'http://127.0.0.1:50021/synthesis*' => Http::response('wav_binary_data', 200, ['Content-Type' => 'audio/wav']),
    ]);

    $response = $this->postJson('/synthesis?speaker=1', ['speedScale' => 1.0]);

    $response->assertOk()
        ->assertHeader('Content-Type', 'audio/wav');
});

test('engine speakers endpoint returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/speakers*' => Http::response([['name' => 'ずんだもん']]),
    ]);

    $response = $this->getJson('/speakers');

    $response->assertOk()
        ->assertJsonFragment(['name' => 'ずんだもん']);
});

test('engine speaker_info endpoint returns info', function () {
    Http::fake([
        'http://127.0.0.1:50021/speaker_info*' => Http::response(['policy' => 'test']),
    ]);

    $response = $this->getJson('/speaker_info?speaker_uuid=388f246b-8c41-4ac1-8e2d-5d79f3ff56d9');

    $response->assertOk()
        ->assertJsonFragment(['policy' => 'test']);
});

test('engine singers endpoint returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/singers*' => Http::response([['name' => 'ずんだもん']]),
    ]);

    $response = $this->getJson('/singers');

    $response->assertOk();
});

test('engine singer_info endpoint returns info', function () {
    Http::fake([
        'http://127.0.0.1:50021/singer_info*' => Http::response(['policy' => 'test']),
    ]);

    $response = $this->getJson('/singer_info?speaker_uuid=388f246b-8c41-4ac1-8e2d-5d79f3ff56d9');

    $response->assertOk();
});

test('engine version endpoint returns version', function () {
    Http::fake([
        'http://127.0.0.1:50021/version' => Http::response('"0.20.0"'),
    ]);

    $response = $this->getJson('/version');

    $response->assertOk();
});
