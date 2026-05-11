<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('coreVersions returns array of version strings', function () {
    Http::fake([
        'http://127.0.0.1:50021/core_versions' => Http::response(['0.14.0', '0.15.0']),
    ]);

    $versions = Voicevox::coreVersions();

    expect($versions)->toBeArray()->toHaveCount(2);
});

test('supportedDevices returns device info array', function () {
    Http::fake([
        'http://127.0.0.1:50021/supported_devices*' => Http::response(['cpu' => true, 'cuda' => false]),
    ]);

    $devices = Voicevox::supportedDevices();

    expect($devices)->toBeArray()->toHaveKey('cpu');
});
