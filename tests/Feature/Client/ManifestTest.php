<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('manifest returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/engine_manifest' => Http::response(['name' => 'test-engine']),
    ]);

    $manifest = Voicevox::manifest();

    expect($manifest)->toBeArray()->toHaveKey('name');
});
