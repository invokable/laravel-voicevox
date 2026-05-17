<?php

declare(strict_types=1);

use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;

test('engine engine_manifest endpoint returns manifest', function () {
    $response = $this->getJson('/engine_manifest');

    $response->assertOk()
        ->assertJsonFragment(['uuid' => '513b0774-3428-4a1d-9d4e-a7fd5f32d0a7']);
});

test('engine presets endpoint returns presets', function () {
    Voicevox::expects('baseUrl->presets')->andReturn([['id' => 1, 'name' => 'default']]);

    $response = $this->getJson('/presets');

    $response->assertOk()
        ->assertJsonFragment(['id' => 1]);
});

test('engine add_preset endpoint returns id', function () {
    Voicevox::expects('baseUrl->addPreset')->andReturn(2);

    $response = $this->postJson('/add_preset', ['id' => 0, 'name' => 'new']);

    $response->assertOk()
        ->assertSee(2);
});

test('engine update_preset endpoint returns id', function () {
    Voicevox::expects('baseUrl->updatePreset')->andReturn(1);

    $response = $this->postJson('/update_preset', ['id' => 1, 'name' => 'updated']);

    $response->assertOk()
        ->assertSee(1);
});

test('engine delete_preset endpoint returns null', function () {
    Voicevox::expects('baseUrl->deletePreset')->andReturn(null);

    $response = $this->postJson('/delete_preset?id=1');

    $response->assertOk();
});

test('engine accent_phrases endpoint returns phrases', function () {
    Synthesizer::expects('createAudioQuery')->andThrow(Exception::class);
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
