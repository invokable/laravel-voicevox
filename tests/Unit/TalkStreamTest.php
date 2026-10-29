<?php

declare(strict_types=1);

use Revolution\Voicevox\Talk\TalkStream;

function talkStream(
    int $totalFrames = 100,
    float $startOffset = 0.0,
    float $segmentLength = 0.3,
    int $samplingRate = 24000,
    bool $stereo = false,
    array &$ranges = [],
): TalkStream {
    $bytesPerFrame = 256 * intdiv($samplingRate, 24000) * ($stereo ? 2 : 1) * 2;

    return new TalkStream(
        totalFrames: $totalFrames,
        samplingRate: $samplingRate,
        stereo: $stereo,
        renderer: function (int $start, int $end) use (&$ranges, $bytesPerFrame): string {
            $ranges[] = [$start, $end];

            return str_repeat("\x01", ($end - $start) * $bytesPerFrame);
        },
        startOffset: $startOffset,
        segmentLength: $segmentLength,
    );
}

test('toFrame rounds half to even like the official engine', function () {
    expect(TalkStream::toFrame(0.0))->toBe(0);
    expect(TalkStream::toFrame(0.3))->toBe(28);
    expect(TalkStream::toFrame(1.0))->toBe(94);
    expect(TalkStream::toFrame(0.04))->toBe(4);
    expect(TalkStream::toFrame(0.016))->toBe(2);
    expect(TalkStream::toFrame(0.008))->toBe(1);
    expect(TalkStream::toFrame(2 / 93.75))->toBe(2);
    expect(TalkStream::toFrame(0.5 / 93.75))->toBe(0);
    expect(TalkStream::toFrame(1.5 / 93.75))->toBe(2);
    expect(TalkStream::toFrame(2.5 / 93.75))->toBe(2);
});

test('header is a valid mono 24kHz wav header', function () {
    $stream = talkStream(totalFrames: 10);

    $header = $stream->header();
    $pcmLength = 10 * 256 * 2;

    expect(strlen($header))->toBe(44);
    expect(substr($header, 0, 4))->toBe('RIFF');
    expect(substr($header, 8, 8))->toBe('WAVEfmt ');
    expect(substr($header, 36, 4))->toBe('data');

    $fields = unpack('VchunkSize/x8/VfmtSize/vformat/vchannels/Vrate/VbyteRate/vblockAlign/vbits/x4/VdataSize', substr($header, 4));

    expect($fields)->toBe([
        'chunkSize' => $pcmLength + 36,
        'fmtSize' => 16,
        'format' => 1,
        'channels' => 1,
        'rate' => 24000,
        'byteRate' => 48000,
        'blockAlign' => 2,
        'bits' => 16,
        'dataSize' => $pcmLength,
    ]);
    expect($stream->contentLength())->toBe($pcmLength + 44);
});

test('header follows output sampling rate and stereo', function () {
    $stream = talkStream(totalFrames: 10, samplingRate: 48000, stereo: true);

    $fields = unpack('vchannels/Vrate/VbyteRate/vblockAlign', substr($stream->header(), 22, 12));

    expect($fields)->toBe([
        'channels' => 2,
        'rate' => 48000,
        'byteRate' => 192000,
        'blockAlign' => 4,
    ]);
    expect($stream->contentLength())->toBe(44 + 10 * 256 * 2 * 2 * 2);
});

test('chunks are rendered in segment sized ranges', function () {
    $ranges = [];
    $stream = talkStream(totalFrames: 100, ranges: $ranges);

    $chunks = iterator_to_array($stream->chunks(), false);

    expect($stream->segmentFrames)->toBe(28);
    expect($ranges)->toBe([[0, 28], [28, 56], [56, 84], [84, 100]]);
    expect($chunks)->toHaveCount(5);
    expect(strlen($chunks[0]))->toBe(44);
});

test('content length matches the streamed body', function () {
    $stream = talkStream(totalFrames: 100);

    expect(strlen($stream->content()))->toBe($stream->contentLength());
});

test('start offset skips leading frames', function () {
    $ranges = [];
    $stream = talkStream(totalFrames: 100, startOffset: 0.3, segmentLength: 0.5, ranges: $ranges);

    expect($stream->startFrame)->toBe(28);
    expect($stream->segmentFrames)->toBe(47);

    $content = $stream->content();

    expect($ranges)->toBe([[28, 75], [75, 100]]);
    expect(strlen($content))->toBe($stream->contentLength());
    expect($stream->contentLength())->toBe(44 + (100 - 28) * 256 * 2);
});

test('start offset beyond the audio length is rejected', function () {
    talkStream(totalFrames: 100, startOffset: 2.0);
})->throws(InvalidArgumentException::class);

test('negative start offset is rejected', function () {
    talkStream(startOffset: -0.1);
})->throws(InvalidArgumentException::class);

test('non positive segment length is rejected', function () {
    talkStream(segmentLength: 0.0);
})->throws(InvalidArgumentException::class);

test('tiny segment length still renders at least one frame', function () {
    $stream = talkStream(totalFrames: 3, segmentLength: 0.001);

    expect($stream->segmentFrames)->toBe(1);
});

test('toResponse streams the wav with content length', function () {
    $stream = talkStream(totalFrames: 10);

    $response = $stream->toResponse(request());

    ob_start();
    $response->sendContent();
    $body = ob_get_clean();

    expect($response->headers->get('Content-Type'))->toBe('audio/wav');
    expect($response->headers->get('Content-Length'))->toBe((string) $stream->contentLength());
    expect($body)->toBe($stream->content());
});
