<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('presets returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/presets' => Http::response([['id' => 1, 'name' => 'test']]),
    ]);

    $presets = Voicevox::presets();

    expect($presets)->toBeArray()->toHaveCount(1);
});

test('add preset returns id', function () {
    Http::fake([
        'http://127.0.0.1:50021/add_preset' => Http::response(2),
    ]);

    $id = Voicevox::addPreset(['id' => 0, 'name' => 'new']);

    expect($id)->toBe(2);
});

test('update preset returns id', function () {
    Http::fake([
        'http://127.0.0.1:50021/update_preset' => Http::response(1),
    ]);

    $id = Voicevox::updatePreset(['id' => 1, 'name' => 'updated']);

    expect($id)->toBe(1);
});

test('delete preset', function () {
    Http::fake([
        'http://127.0.0.1:50021/delete_preset*' => Http::response(),
    ]);

    Voicevox::deletePreset(1);

    Http::assertSentCount(1);
});
