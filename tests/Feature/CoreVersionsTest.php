<?php

declare(strict_types=1);

use Revolution\Voicevox\Voicevox;

test('coreVersions returns array of version strings', function () {
    Voicevox::expects('coreVersions')->andReturn(['0.14.0', '0.15.0']);

    $versions = Voicevox::coreVersions();

    expect($versions)->toBeArray()->toHaveCount(2);
});

test('supportedDevices returns device info array', function () {
    Voicevox::expects('supportedDevices')->andReturn(['cpu' => true, 'cuda' => false]);

    $devices = Voicevox::supportedDevices();

    expect($devices)->toBeArray()->toHaveKey('cpu');
});
