<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('setting returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/setting' => Http::response(['setting_key' => 'setting_value']),
    ]);

    $setting = Voicevox::setting();

    expect($setting)->toBeArray()->toHaveKey('setting_key');
});

test('updateSetting sends form request', function () {
    Http::fake([
        'http://127.0.0.1:50021/setting' => Http::response(null, 204),
    ]);

    Voicevox::updateSetting(['cors_policy_mode' => 'localapps']);

    Http::assertSent(fn ($req) => str_contains($req->url(), 'setting'));
});
