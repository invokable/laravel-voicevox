<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

use function Revolution\Voicevox\dict;
use function Revolution\Voicevox\preset;

beforeEach(function () {
    $corePath = rtrim((string) config('voicevox.core.path', ''), '/');
    $coreLibPath = $corePath.'/c_api/lib/libvoicevox_core.so';

    if ($corePath === '' || ! File::exists($coreLibPath)) {
        $this->markTestSkipped('VOICEVOX core runtime is not configured.');
    }
});

test('native user dict can add and retrieve word', function () {
    $userDict = dict();
    $uuid = $userDict->add(
        surface: 'テスト単語',
        pronunciation: 'テストタンゴ',
        accentType: 1,
        wordType: 'PROPER_NOUN',
    );

    $words = $userDict->all();
    expect($uuid)->toBeString()
        ->and($words)->toBeArray()
        ->and(collect($words)->contains(function (mixed $word): bool {
            return ($word['surface'] ?? null) === 'テスト単語'
                && ($word['pronunciation'] ?? null) === 'テストタンゴ';
        }))->toBeTrue();

    $userDict->delete($uuid);
});

test('engine user_dict endpoint returns dictionary', function () {
    $response = $this->getJson('/user_dict')
        ->assertOk();

    expect($response->json())->toBeArray();
});

test('engine add and delete user_dict word workflow', function () {
    $word = [
        'surface' => 'エンジンテスト',
        'pronunciation' => 'エンジンテスト',
        'accent_type' => 1,
        'word_type' => 'PROPER_NOUN',
    ];

    $addResponse = $this->postJson('/user_dict_word', $word)
        ->assertOk();

    $uuid = $addResponse->json();
    expect($uuid)->toBeString();

    // Verify word exists in user_dict
    $dictResponse = $this->getJson('/user_dict')
        ->assertOk()
        ->json();

    expect($dictResponse)->toBeArray()
        ->and(collect($dictResponse)->contains(function (mixed $entry): bool {
            return ($entry['surface'] ?? null) === 'エンジンテスト';
        }))->toBeTrue();

    $this->deleteJson("/user_dict_word/{$uuid}")
        ->assertNoContent();
});

test('native preset store can add, find and delete preset', function () {
    $presetStore = preset();

    $newPreset = [
        'id' => 99999,
        'name' => '統合テストプリセット',
        'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
        'style_id' => 1,
        'speedScale' => 1.0,
        'pitchScale' => 0.0,
        'intonationScale' => 1.0,
        'volumeScale' => 1.0,
        'prePhonemeLength' => 0.1,
        'postPhonemeLength' => 0.1,
    ];

    $id = $presetStore->add($newPreset);
    expect($id)->toBe(99999);

    $found = $presetStore->find($id);
    expect($found)->toBeArray()
        ->and($found['name'] ?? null)->toBe('統合テストプリセット');

    $presetStore->delete($id);
    expect($presetStore->find($id))->toBeNull();
});

test('engine presets endpoint returns array', function () {
    $response = $this->getJson('/presets')
        ->assertOk()
        ->json();

    expect($response)->toBeArray();
});

test('engine add, update and delete preset workflow', function () {
    $newPreset = [
        'id' => 88888,
        'name' => 'エンジンプリセット',
        'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
        'style_id' => 1,
        'speedScale' => 1.2,
        'pitchScale' => 0.0,
        'intonationScale' => 1.0,
        'volumeScale' => 1.0,
        'prePhonemeLength' => 0.1,
        'postPhonemeLength' => 0.1,
    ];

    $addResponse = $this->postJson('/add_preset', $newPreset)
        ->assertOk()
        ->json();

    expect($addResponse)->toBe(88888);

    $updatedPreset = array_merge($newPreset, ['speedScale' => 1.5]);
    $this->postJson('/update_preset', $updatedPreset)
        ->assertOk();

    $this->postJson('/delete_preset', ['id' => 88888])
        ->assertNoContent();
});

test('engine speakers endpoint returns speaker metadata', function () {
    $response = $this->getJson('/speakers')
        ->assertOk()
        ->json();

    expect($response)->toBeArray()->not->toBeEmpty();
});

test('engine speaker_info endpoint returns detailed info', function () {
    $response = $this->getJson('/speaker_info?speaker_uuid=7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff');

    // speaker_info requires character_info resources which may not be installed
    if ($response->status() === 501) {
        $this->markTestSkipped('speaker_info requires character_info resources.');
    }

    $response->assertOk();
    $data = $response->json();

    expect($data)->toBeArray()
        ->and($data['policy'] ?? null)->toBeString();
});

test('engine supported_devices endpoint returns device list', function () {
    $response = $this->getJson('/supported_devices');

    // supported_devices has no native implementation and falls back to client
    if ($response->status() === 501) {
        $this->markTestSkipped('supported_devices requires fallback engine connection.');
    }

    $response->assertOk();
    $data = $response->json();

    expect($data)->toBeArray()
        ->toHaveKey('cpu')
        ->toHaveKey('cuda')
        ->toHaveKey('dml');
});

test('engine version endpoint returns version string', function () {
    $response = $this->getJson('/version')
        ->assertOk()
        ->getContent();

    expect($response)->toBeString()->not->toBeEmpty();
});

test('engine core_versions endpoint returns version array', function () {
    $response = $this->getJson('/core_versions')
        ->assertOk()
        ->json();

    expect($response)->toBeArray()->not->toBeEmpty();
});

test('engine manifest endpoint returns manifest json', function () {
    $response = $this->getJson('/engine_manifest')
        ->assertOk()
        ->json();

    expect($response)->toBeArray()
        ->toHaveKey('manifest_version')
        ->toHaveKey('name')
        ->toHaveKey('supported_features');
});
