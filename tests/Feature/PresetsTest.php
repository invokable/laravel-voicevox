<?php

declare(strict_types=1);

use Revolution\Voicevox\Voicevox;

test('presets returns array', function () {
    Voicevox::expects('presets')->andReturn([['id' => 1, 'name' => 'test']]);

    $presets = Voicevox::presets();

    expect($presets)->toBeArray()->toHaveCount(1);
});

test('add preset returns id', function () {
    Voicevox::expects('addPreset')->andReturn(2);

    $id = Voicevox::addPreset(['id' => 0, 'name' => 'new']);

    expect($id)->toBe(2);
});

test('update preset returns id', function () {
    Voicevox::expects('updatePreset')->andReturn(1);

    $id = Voicevox::updatePreset(['id' => 1, 'name' => 'updated']);

    expect($id)->toBe(1);
});

test('delete preset', function () {
    Voicevox::expects('deletePreset')->with(1);

    Voicevox::deletePreset(1);
});
