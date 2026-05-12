<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('installLibrary sends request', function () {
    Http::fake([
        'http://127.0.0.1:50021/install_library/test-uuid' => Http::response(null, 204),
    ]);

    Voicevox::installLibrary('test-uuid');

    Http::assertSent(fn ($req) => str_contains($req->url(), 'install_library/test-uuid'));
});

test('uninstallLibrary sends request', function () {
    Http::fake([
        'http://127.0.0.1:50021/uninstall_library/test-uuid' => Http::response(null, 204),
    ]);

    Voicevox::uninstallLibrary('test-uuid');

    Http::assertSent(fn ($req) => str_contains($req->url(), 'uninstall_library/test-uuid'));
});
