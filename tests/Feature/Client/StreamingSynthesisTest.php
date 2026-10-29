<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Revolution\Voicevox\Voicevox;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('streamingSynthesis returns wav binary', function () {
    Http::fake([
        'http://127.0.0.1:50021/streaming_synthesis*' => Http::response('RIFF-wav-data'),
    ]);

    $audio = Voicevox::streamingSynthesis(['speedScale' => 1.0]);

    expect($audio)->toBe('RIFF-wav-data');
});

test('streamingSynthesis sends default parameters', function () {
    Http::fake([
        'http://127.0.0.1:50021/streaming_synthesis*' => Http::response('wav'),
    ]);

    Voicevox::streamingSynthesis(['speedScale' => 1.0]);

    Http::assertSent(function (Request $request) {
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return $request->method() === 'POST'
            && str_starts_with($request->url(), 'http://127.0.0.1:50021/streaming_synthesis')
            && $request->hasHeader('Accept', 'audio/wav')
            && $query['speaker'] === '1'
            && $query['start_offset'] === '0'
            && $query['segment_length'] === '0.3'
            && $query['enable_interrogative_upspeak'] === '1'
            && $request['speedScale'] === 1.0;
    });
});

test('streamingSynthesis sends custom parameters', function () {
    Http::fake([
        'http://127.0.0.1:50021/streaming_synthesis*' => Http::response('wav'),
    ]);

    Voicevox::streamingSynthesis(
        audioQuery: ['speedScale' => 1.0],
        id: 3,
        startOffset: 1.5,
        segmentLength: 0.5,
        enableInterrogativeUpspeak: false,
    );

    Http::assertSent(function (Request $request) {
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query['speaker'] === '3'
            && $query['start_offset'] === '1.5'
            && $query['segment_length'] === '0.5'
            && $query['enable_interrogative_upspeak'] === '0';
    });
});

test('streamingSynthesis throws on error response', function () {
    Http::fake([
        'http://127.0.0.1:50021/streaming_synthesis*' => Http::response(['detail' => 'bad'], 400),
    ]);

    Voicevox::streamingSynthesis(['speedScale' => 1.0], startOffset: 100.0);
})->throws(RequestException::class);
