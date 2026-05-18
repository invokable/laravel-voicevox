<?php

declare(strict_types=1);

use Revolution\Voicevox\Engine\NativePresetStore;

beforeEach(function () {
    $this->tmpFile = sys_get_temp_dir().'/voicevox_presets_test_'.uniqid().'.json';
});

afterEach(function () {
    if (file_exists($this->tmpFile)) {
        unlink($this->tmpFile);
    }
});

test('NativePresetStore starts empty when no file exists', function () {
    $store = new NativePresetStore($this->tmpFile);
    expect($store->all())->toBeArray()->toBeEmpty();
});

test('NativePresetStore add returns auto-incremented id', function () {
    $store = new NativePresetStore($this->tmpFile);

    $id = $store->add(['id' => 0, 'name' => 'test', 'style_id' => 1]);

    expect($id)->toBe(1);
    expect($store->all())->toHaveCount(1);
});

test('NativePresetStore add respects requested id when free', function () {
    $store = new NativePresetStore($this->tmpFile);

    $id = $store->add(['id' => 42, 'name' => 'test', 'style_id' => 1]);

    expect($id)->toBe(42);
});

test('NativePresetStore add auto-increments when id already taken', function () {
    $store = new NativePresetStore($this->tmpFile);

    $store->add(['id' => 1, 'name' => 'first', 'style_id' => 1]);
    $id = $store->add(['id' => 1, 'name' => 'dup', 'style_id' => 2]);

    expect($id)->toBe(2);
    expect($store->all())->toHaveCount(2);
});

test('NativePresetStore find returns preset by id', function () {
    $store = new NativePresetStore($this->tmpFile);

    $store->add(['id' => 5, 'name' => 'mypreset', 'style_id' => 3]);

    $preset = $store->find(5);
    expect($preset)->toBeArray()
        ->and($preset['name'])->toBe('mypreset');
});

test('NativePresetStore find returns null for missing id', function () {
    $store = new NativePresetStore($this->tmpFile);

    expect($store->find(999))->toBeNull();
});

test('NativePresetStore update replaces preset', function () {
    $store = new NativePresetStore($this->tmpFile);

    $store->add(['id' => 1, 'name' => 'original', 'style_id' => 1]);
    $returnedId = $store->update(['id' => 1, 'name' => 'changed', 'style_id' => 2]);

    expect($returnedId)->toBe(1);
    expect($store->find(1)['name'])->toBe('changed');
    expect($store->all())->toHaveCount(1);
});

test('NativePresetStore delete removes preset', function () {
    $store = new NativePresetStore($this->tmpFile);

    $store->add(['id' => 1, 'name' => 'to-delete', 'style_id' => 1]);
    $store->delete(1);

    expect($store->all())->toBeEmpty();
    expect($store->find(1))->toBeNull();
});

test('NativePresetStore persists data to file', function () {
    $store = new NativePresetStore($this->tmpFile);

    $store->add(['id' => 7, 'name' => 'persistent', 'style_id' => 1]);

    // A fresh store loaded from the same file should see the preset
    $store2 = new NativePresetStore($this->tmpFile);
    $preset = $store2->find(7);
    expect($preset)->not->toBeNull()
        ->and($preset['name'])->toBe('persistent');
});
