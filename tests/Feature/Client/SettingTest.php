<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('setting returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/setting' => Http::response('html'),
    ]);

    $setting = Voicevox::setting();

    expect($setting)->toBeString()->toBe('html');
});

test('updateSetting sends form request', function () {
    Http::fake([
        'http://127.0.0.1:50021/setting' => Http::response(null, 204),
    ]);

    Voicevox::updateSetting(['cors_policy_mode' => 'localapps']);

    Http::assertSent(fn ($req) => str_contains($req->url(), 'setting'));
});
