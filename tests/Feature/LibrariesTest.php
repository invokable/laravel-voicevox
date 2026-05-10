<?php

declare(strict_types=1);

use Revolution\Voicevox\Voicevox;

test('downloadableLibraries returns array', function () {
    Voicevox::expects('downloadableLibraries')->andReturn([['name' => 'SomeLibrary', 'uuid' => 'abc']]);

    $libs = Voicevox::downloadableLibraries();

    expect($libs)->toBeArray()->toHaveCount(1);
});

test('installedLibraries returns array', function () {
    Voicevox::expects('installedLibraries')->andReturn(['abc' => ['name' => 'SomeLibrary', 'version' => '1.0.0']]);

    $libs = Voicevox::installedLibraries();

    expect($libs)->toBeArray()->toHaveKey('abc');
});
