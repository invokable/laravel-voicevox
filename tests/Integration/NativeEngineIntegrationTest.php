<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Revolution\Voicevox\Core\Enums\UserDictWordType;

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
    $uuid = $userDict->addWord(
        surface: 'テスト単語',
        pronunciation: 'テストタンゴ',
        accentType: 1,
        wordType: UserDictWordType::PROPER_NOUN,
    );

    expect($uuid)->toBeString()
        ->and($userDict->all())->toHaveKey($uuid);

    $word = $userDict->getWord($uuid);
    expect($word)->toBeArray()
        ->and($word['surface'] ?? null)->toBe('テスト単語')
        ->and($word['pronunciation'] ?? null)->toBe('テストタンゴ');

    $userDict->removeWord($uuid);
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
        ->assertCreated();

    $uuid = $addResponse->json('word_uuid');
    expect($uuid)->toBeString();

    $getResponse = $this->getJson("/user_dict_word/{$uuid}")
        ->assertOk()
        ->json();

    expect($getResponse['surface'])->toBe('エンジンテスト');

    $this->deleteJson("/user_dict_word/{$uuid}")
        ->assertNoContent();

    $this->getJson("/user_dict_word/{$uuid}")
        ->assertNotFound();
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
        ->assertCreated()
        ->json();

    expect($addResponse)->toBe(88888);

    $updatedPreset = array_merge($newPreset, ['speedScale' => 1.5]);
    $this->postJson('/update_preset', $updatedPreset)
        ->assertNoContent();

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
    $response = $this->getJson('/speaker_info?speaker_uuid=7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff')
        ->assertOk()
        ->json();

    expect($response)->toBeArray()
        ->and($response['policy'] ?? null)->toBeString();
});

test('engine supported_devices endpoint returns device list', function () {
    $response = $this->getJson('/supported_devices')
        ->assertOk()
        ->json();

    expect($response)->toBeArray()
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
