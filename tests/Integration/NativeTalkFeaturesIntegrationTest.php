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

test('engine accent_phrases endpoint generates accent phrases from text', function () {
    $response = $this->postJson('/accent_phrases?text=こんにちは&speaker=1')
        ->assertOk()
        ->json();

    expect($response)->toBeArray()->not->toBeEmpty()
        ->and($response[0] ?? null)->toHaveKey('moras')
        ->and($response[0] ?? null)->toHaveKey('accent');
});

test('engine mora_pitch endpoint adjusts pitch for accent phrases', function () {
    // First get accent phrases
    $accentPhrases = $this->postJson('/accent_phrases?text=テスト&speaker=1')
        ->assertOk()
        ->json();

    expect($accentPhrases)->toBeArray()->not->toBeEmpty();

    // Apply mora_pitch adjustment
    $response = $this->postJson('/mora_pitch?speaker=1', $accentPhrases)
        ->assertOk()
        ->json();

    expect($response)->toBeArray()->not->toBeEmpty()
        ->and($response[0]['moras'][0] ?? null)->toHaveKey('pitch');
});

test('engine mora_length endpoint adjusts length for accent phrases', function () {
    $accentPhrases = $this->postJson('/accent_phrases?text=こんにちは&speaker=1')
        ->assertOk()
        ->json();

    expect($accentPhrases)->toBeArray()->not->toBeEmpty();

    $response = $this->postJson('/mora_length?speaker=1', $accentPhrases)
        ->assertOk()
        ->json();

    expect($response)->toBeArray()->not->toBeEmpty()
        ->and($response[0]['moras'][0] ?? null)->toHaveKey('vowel_length');
});

test('engine mora_data endpoint processes accent phrases', function () {
    $accentPhrases = $this->postJson('/accent_phrases?text=よろしく&speaker=1')
        ->assertOk()
        ->json();

    expect($accentPhrases)->toBeArray()->not->toBeEmpty();

    $response = $this->postJson('/mora_data?speaker=1', $accentPhrases)
        ->assertOk()
        ->json();

    expect($response)->toBeArray()->not->toBeEmpty()
        ->and($response[0]['moras'][0] ?? null)->toHaveKey('pitch')
        ->and($response[0]['moras'][0] ?? null)->toHaveKey('vowel_length');
});

test('engine validate_kana endpoint accepts valid kana text', function () {
    $response = $this->postJson('/validate_kana', ['text' => 'コンニチワ'])
        ->assertOk();

    expect($response->json())->toBeArray();
});

test('engine validate_kana endpoint rejects invalid text', function () {
    $response = $this->postJson('/validate_kana', ['text' => 'こんにちは123'])
        ->assertStatus(422);
});

test('engine update_user_dict_word workflow', function () {
    $word = [
        'surface' => '更新テスト',
        'pronunciation' => 'コウシンテスト',
        'accent_type' => 1,
        'word_type' => 'PROPER_NOUN',
    ];

    $uuid = $this->postJson('/user_dict_word', $word)
        ->assertOk()
        ->json();

    expect($uuid)->toBeString();

    // Update the word
    $updatedWord = array_merge($word, [
        'surface' => '更新済み',
        'pronunciation' => 'コウシンズミ',
    ]);

    $this->putJson("/user_dict_word/{$uuid}", $updatedWord)
        ->assertNoContent();

    // Verify update
    $dict = $this->getJson('/user_dict')
        ->assertOk()
        ->json();

    expect(collect($dict)->contains(function (mixed $entry) {
        return ($entry['surface'] ?? null) === '更新済み';
    }))->toBeTrue();

    // Cleanup
    $this->deleteJson("/user_dict_word/{$uuid}")
        ->assertNoContent();
});

test('engine audio_query_from_preset generates query using preset', function () {
    $newPreset = [
        'id' => 77777,
        'name' => 'プリセットクエリテスト',
        'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
        'style_id' => 1,
        'speedScale' => 1.3,
        'pitchScale' => 0.1,
        'intonationScale' => 1.2,
        'volumeScale' => 1.0,
        'prePhonemeLength' => 0.1,
        'postPhonemeLength' => 0.1,
    ];

    $presetId = $this->postJson('/add_preset', $newPreset)
        ->assertOk()
        ->json();

    expect($presetId)->toBe(77777);

    $audioQuery = $this->postJson("/audio_query_from_preset?text=プリセットテスト&preset_id={$presetId}")
        ->assertOk()
        ->json();

    expect($audioQuery)->toBeArray()
        ->toHaveKey('speedScale')
        ->and($audioQuery['speedScale'])->toBe(1.3);

    // Cleanup
    $this->postJson('/delete_preset', ['id' => $presetId])
        ->assertNoContent();
});
