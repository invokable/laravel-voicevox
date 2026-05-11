<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('import user dict', function () {
    Http::fake([
        'http://127.0.0.1:50021/import_user_dict*' => Http::response(),
    ]);

    Voicevox::importUserDict([], false);

    Http::assertSentCount(1);
});

test('import user dict with override', function () {
    Http::fake([
        'http://127.0.0.1:50021/import_user_dict*' => Http::response(),
    ]);

    Voicevox::importUserDict(['word' => 'test'], true);

    Http::assertSentCount(1);
});
