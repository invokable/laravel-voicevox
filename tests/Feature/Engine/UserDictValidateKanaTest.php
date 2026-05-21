<?php

declare(strict_types=1);

use Revolution\Voicevox\Engine\NativeUserDict;
use Revolution\Voicevox\Voicevox;

test('engine user_dict endpoint returns dict', function () {
    $this->mock(NativeUserDict::class, function ($mock) {
        $mock->allows('toArray')->andReturn(['uuid-1' => ['surface' => 'テスト']]);
    });

    $response = $this->getJson('/user_dict');

    $response->assertOk()
        ->assertJsonFragment(['surface' => 'テスト']);
});

test('engine user_dict_word POST endpoint returns uuid', function () {
    $this->mock(NativeUserDict::class, function ($mock) {
        $mock->allows('addWord')->andReturn('new-uuid');
    });

    $response = $this->postJson('/user_dict_word?surface=テスト&pronunciation=テスト&accent_type=0');

    $response->assertOk()
        ->assertSee('new-uuid');
});

test('engine user_dict_word PUT endpoint returns 204', function () {
    $this->mock(NativeUserDict::class, function ($mock) {
        $mock->allows('updateWord')->andReturn(null);
    });

    $response = $this->putJson('/user_dict_word/some-uuid?surface=テスト&pronunciation=テスト&accent_type=0');

    $response->assertNoContent();
});

test('engine user_dict_word DELETE endpoint returns 204', function () {
    $this->mock(NativeUserDict::class, function ($mock) {
        $mock->allows('removeWord')->andReturn(null);
    });

    $response = $this->deleteJson('/user_dict_word/some-uuid');

    $response->assertNoContent();
});

test('engine import_user_dict endpoint returns 204', function () {
    Voicevox::expects('baseUrl->importUserDict')->andReturn(null);

    $response = $this->postJson('/import_user_dict?override=false', []);

    $response->assertNoContent();
});

test('engine validate_kana endpoint returns bool', function () {
    $text = rawurlencode("ズ'ンダモン");
    $response = $this->postJson("/validate_kana?text=$text");

    $response->assertOk()
        ->assertSee('true');
});

test('engine validate_kana endpoint returns 400 on invalid kana', function () {
    $text = rawurlencode('テスト');
    $response = $this->postJson("/validate_kana?text=$text");

    $response->assertStatus(400)
        ->assertJsonFragment(['error_name' => 'AccentNotFound']);
});

test('engine audio_query_from_preset endpoint returns audio query', function () {
    $audioQuery = ['speedScale' => 1.0, 'pitchScale' => 0.0];
    Voicevox::expects('baseUrl->audioQueryFromPreset')->andReturn($audioQuery);

    $response = $this->postJson('/audio_query_from_preset?text=テスト&preset_id=1');

    $response->assertOk()
        ->assertJsonFragment(['speedScale' => 1.0]);
});

test('engine user_dict falls back to 501 when engine unavailable', function () {
    $this->mock(NativeUserDict::class, function ($mock) {
        $mock->allows('toArray')->andThrow(Exception::class);
    });

    $response = $this->getJson('/user_dict');

    $response->assertStatus(501);
});
