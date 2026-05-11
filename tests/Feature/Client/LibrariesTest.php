<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('downloadableLibraries returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/downloadable_libraries' => Http::response([['name' => 'SomeLibrary', 'uuid' => 'abc']]),
    ]);

    $libs = Voicevox::downloadableLibraries();

    expect($libs)->toBeArray()->toHaveCount(1);
});

test('installedLibraries returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/installed_libraries' => Http::response(['abc' => ['name' => 'SomeLibrary', 'version' => '1.0.0']]),
    ]);

    $libs = Voicevox::installedLibraries();

    expect($libs)->toBeArray()->toHaveKey('abc');
});
