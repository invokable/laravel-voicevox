<?php

declare(strict_types=1);

use Revolution\Voicevox\Engine\NativePresetStore;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;

// ---- engine_manifest ----

test('engine engine_manifest endpoint returns manifest', function () {
    $response = $this->getJson('/engine_manifest');

    $response->assertOk()
        ->assertJsonFragment(['uuid' => '513b0774-3428-4a1d-9d4e-a7fd5f32d0a7']);
});

// ---- presets (native NativePresetStore) ----

test('engine presets endpoint returns presets from store', function () {
    $store = Mockery::mock(NativePresetStore::class);
    $store->allows('all')->andReturn([['id' => 1, 'name' => 'default']]);
    $this->app->instance(NativePresetStore::class, $store);

    $response = $this->getJson('/presets');

    $response->assertOk()
        ->assertJsonFragment(['id' => 1]);
});

test('engine presets endpoint falls back to Voicevox when store throws', function () {
    $this->mock(NativePresetStore::class, function ($mock) {
        $mock->allows('all')->andReturn([['id' => 2, 'name' => 'fallback']]);
    });

    $response = $this->getJson('/presets');

    $response->assertOk()
        ->assertJsonFragment(['id' => 2]);
});

test('engine add_preset endpoint returns id from store', function () {
    $this->mock(NativePresetStore::class, function ($mock) {
        $mock->allows('add')->andReturn(5);
    });

    $response = $this->postJson('/add_preset', ['id' => 0, 'name' => 'new']);

    $response->assertOk()
        ->assertSee(5);
});

test('engine update_preset endpoint returns id from store', function () {
    $store = Mockery::mock(NativePresetStore::class);
    $store->allows('update')->andReturn(1);
    $this->app->instance(NativePresetStore::class, $store);

    $response = $this->postJson('/update_preset', ['id' => 1, 'name' => 'updated']);

    $response->assertOk()
        ->assertSee(1);
});

test('engine delete_preset endpoint returns 204 from store', function () {
    $store = Mockery::mock(NativePresetStore::class);
    $store->allows('delete')->andReturn(null);
    $this->app->instance(NativePresetStore::class, $store);

    $response = $this->postJson('/delete_preset?id=1');

    $response->assertNoContent();
});

test('engine audio_query_from_preset uses store preset and synthesizer', function () {
    $preset = [
        'id' => 1,
        'name' => 'fast',
        'style_id' => 3,
        'speedScale' => 1.5,
        'pitchScale' => 0.0,
        'intonationScale' => 1.0,
        'volumeScale' => 1.0,
        'prePhonemeLength' => 0.1,
        'postPhonemeLength' => 0.1,
    ];
    $store = Mockery::mock(NativePresetStore::class);
    $store->allows('find')->with(1)->andReturn($preset);
    $this->app->instance(NativePresetStore::class, $store);

    $audioQuery = json_encode(['speedScale' => 1.0, 'pitchScale' => 0.0, 'accent_phrases' => []]);
    Synthesizer::expects('createAudioQuery')->with('テスト', 3)->andReturn($audioQuery);

    $text = rawurlencode('テスト');
    $response = $this->postJson("/audio_query_from_preset?text=$text&preset_id=1");

    $response->assertOk()
        ->assertJsonFragment(['speedScale' => 1.5]);
});

test('engine audio_query_from_preset returns 501 when preset not found', function () {
    $store = Mockery::mock(NativePresetStore::class);
    $store->allows('find')->andReturn(null);
    $this->app->instance(NativePresetStore::class, $store);

    $response = $this->postJson('/audio_query_from_preset?text=テスト&preset_id=99');

    $response->assertStatus(501);
});

// ---- accent_phrases / mora ----

test('engine accent_phrases endpoint returns phrases', function () {
    Synthesizer::expects('createAccentPhrases')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->accentPhrases')->andReturn([['moras' => []]]);

    $response = $this->postJson('/accent_phrases?text=テスト&speaker=1&is_kana=false');

    $response->assertOk()
        ->assertJsonFragment(['moras' => []]);
});

test('engine mora_data endpoint returns mora data', function () {
    Synthesizer::expects('replaceMoraData')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->moraData')->andReturn([['moras' => [['vowel' => 'a']]]]);

    $response = $this->postJson('/mora_data?speaker=1', [['moras' => []]]);

    $response->assertOk();
});

test('engine mora_length endpoint returns mora length', function () {
    Synthesizer::expects('replacePhonemeLength')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->moraLength')->andReturn([['moras' => [['vowel_length' => 0.1]]]]);

    $response = $this->postJson('/mora_length?speaker=1', [['moras' => []]]);

    $response->assertOk();
});

test('engine mora_pitch endpoint returns mora pitch', function () {
    Synthesizer::expects('replaceMoraPitch')->andThrow(Exception::class);
    Voicevox::expects('baseUrl->moraPitch')->andReturn([['moras' => [['pitch' => 5.0]]]]);

    $response = $this->postJson('/mora_pitch?speaker=1', [['moras' => []]]);

    $response->assertOk();
});
