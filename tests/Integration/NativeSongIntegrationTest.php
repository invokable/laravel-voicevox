<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Revolution\Voicevox\Song\Note;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\Synthesizer;

use function Revolution\Voicevox\song;

beforeEach(function () {
    $corePath = rtrim((string) config('voicevox.core.path', ''), '/');
    $coreLibPath = $corePath.'/c_api/lib/libvoicevox_core.so';

    if ($corePath === '' || ! File::exists($coreLibPath)) {
        $this->markTestSkipped('VOICEVOX core runtime is not configured.');
    }
});

test('native song helper generates wav audio from Score', function () {
    $score = Score::make([
        Note::make(length: 10, lyric: '', key: null, id: 'rest1'),
        Note::make(length: 100, lyric: 'ど', key: 60, id: 'note1'),
        Note::make(length: 100, lyric: 'れ', key: 62, id: 'note2'),
    ]);

    $response = song($score, teacher: 6000)->generate(id: 6000);
    $content = $response->content();

    expect($content)->toStartWith('RIFF')
        ->and(strlen($content))->toBeGreaterThan(44);
});

test('native song helper with array score generates wav audio', function () {
    $score = [
        'notes' => [
            ['frame_length' => 10, 'lyric' => '', 'key' => null],
            ['frame_length' => 100, 'lyric' => 'ど', 'key' => 60],
            ['frame_length' => 100, 'lyric' => 'れ', 'key' => 62],
        ],
    ];

    $response = song($score, teacher: 6000)->generate(id: 6000);
    $content = $response->content();

    expect($content)->toStartWith('RIFF')
        ->and(strlen($content))->toBeGreaterThan(44);
});

test('SongAudioQuery sync method updates f0 and volume', function () {
    $score = Score::make([
        Note::make(length: 10, lyric: '', key: null),
        Note::make(length: 100, lyric: 'あ', key: 60),
    ]);

    $query = song($score, teacher: 6000);
    $originalF0 = $query->frameAudioQuery['f0'];

    $query->sync();

    expect($query->frameAudioQuery['f0'])->toBeArray()
        ->and($query->frameAudioQuery['volume'])->toBeArray();
});

test('SongAudioQuery updateF0 method works', function () {
    $score = Score::make([
        Note::make(length: 10, lyric: '', key: null),
        Note::make(length: 100, lyric: 'い', key: 62),
    ]);

    $query = song($score, teacher: 6000);
    $query->updateF0();

    expect($query->frameAudioQuery['f0'])->toBeArray()->not->toBeEmpty();
});

test('SongAudioQuery updateVolume method works', function () {
    $score = Score::make([
        Note::make(length: 10, lyric: '', key: null),
        Note::make(length: 100, lyric: 'う', key: 64),
    ]);

    $query = song($score, teacher: 6000);
    $query->updateVolume();

    expect($query->frameAudioQuery['volume'])->toBeArray()->not->toBeEmpty();
});

test('Note::len helper calculates frame length correctly', function () {
    $quarterNote = Note::len(ticks: 480, bpm: 120);

    expect($quarterNote)->toBeInt()->toBeGreaterThan(0);
});

test('engine sing_frame_audio_query and frame_synthesis work with native core', function () {
    $score = [
        'notes' => [
            ['frame_length' => 10, 'lyric' => '', 'key' => null],
            ['frame_length' => 100, 'lyric' => 'ど', 'key' => 60],
        ],
    ];

    $frameAudioQuery = $this->postJson('/sing_frame_audio_query?speaker=6000', $score)
        ->assertOk()
        ->json();

    expect($frameAudioQuery)->toBeArray()
        ->toHaveKey('f0')
        ->toHaveKey('volume')
        ->toHaveKey('phonemes');

    $response = $this->postJson('/frame_synthesis?speaker=6000', $frameAudioQuery)
        ->assertOk()
        ->assertHeader('Content-Type', 'audio/wav');

    expect($response->getContent())->toStartWith('RIFF');
});

test('native synthesizer createSingFrameAudioQuery returns json', function () {
    $score = json_encode([
        'notes' => [
            ['frame_length' => 10, 'lyric' => '', 'key' => null],
            ['frame_length' => 100, 'lyric' => 'か', 'key' => 60],
        ],
    ]);

    $result = Synthesizer::createSingFrameAudioQuery($score, 6000);
    $decoded = json_decode($result, true);

    expect($decoded)->toBeArray()
        ->toHaveKey('f0')
        ->toHaveKey('volume')
        ->toHaveKey('phonemes');
});

test('native synthesizer frameSynthesis generates wav from frame audio query', function () {
    $score = json_encode([
        'notes' => [
            ['frame_length' => 10, 'lyric' => '', 'key' => null],
            ['frame_length' => 80, 'lyric' => 'き', 'key' => 62],
        ],
    ]);

    $frameAudioQuery = Synthesizer::createSingFrameAudioQuery($score, 6000);
    $audio = Synthesizer::frameSynthesis($frameAudioQuery, 6000);

    expect($audio)->toStartWith('RIFF')
        ->and(strlen($audio))->toBeGreaterThan(44);
});
