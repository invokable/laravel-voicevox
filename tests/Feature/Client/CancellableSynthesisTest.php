<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('cancellableSynthesis returns wav body', function () {
    Http::fake([
        'http://127.0.0.1:50021/cancellable_synthesis*' => Http::response('wav-binary'),
    ]);

    $body = Voicevox::cancellableSynthesis(['speedScale' => 1.0], id: 1);

    expect($body)->toBe('wav-binary');
});

test('alive returns html body', function () {
    Http::fake([
        'http://127.0.0.1:50021/' => Http::response('<html>portal</html>'),
    ]);

    $html = Voicevox::alive();

    expect($html)->toContain('portal');
});
