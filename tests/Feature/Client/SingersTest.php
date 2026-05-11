<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('singers returns array', function () {
    Http::fake([
        'http://127.0.0.1:50021/singers*' => Http::response([['name' => 'ずんだもん', 'styles' => []]]),
    ]);

    $singers = Voicevox::singers();

    expect($singers)->toBeArray()->toHaveCount(1);
});

test('singer returns info array', function () {
    Http::fake([
        'http://127.0.0.1:50021/singer_info*' => Http::response(['policy' => '', 'portrait' => '']),
    ]);

    $info = Voicevox::singer('388f246b-8c41-4ac1-8e2d-5d79f3ff56d9');

    expect($info)->toBeArray()->toHaveKey('policy');
});
