<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Revolution\Voicevox\Core\VoicevoxCore;
use Revolution\Voicevox\Synthesizer;

use function Revolution\Voicevox\kana;
use function Revolution\Voicevox\preset;
use function Revolution\Voicevox\talk;

beforeEach(function () {
    $corePath = rtrim((string) config('voicevox.core.path', ''), '/');
    $coreLibPath = $corePath.'/c_api/lib/libvoicevox_core.so';

    if ($corePath === '' || ! File::exists($coreLibPath)) {
        $coreLibPath = $corePath.'/c_api/lib/libvoicevox_core.dylib';
    }

    if ($corePath === '' || ! File::exists($coreLibPath)) {
        $this->markTestSkipped('VOICEVOX core runtime is not configured.');
    }
});

test('native synthesizer metas returns json array', function () {
    $metas = json_decode(Synthesizer::metas(), true);

    expect($metas)->toBeArray()->not->toBeEmpty();
});

test('native talk helper generates wav audio', function () {
    $response = talk('統合テストなのだ', id: 1)->generate(id: 1);
    $content = $response->content();

    expect($content)->toStartWith('RIFF')
        ->and(strlen($content))->toBeGreaterThan(44);
});

test('native kana helper generates wav audio', function () {
    $response = kana('コ\'ンニチワ', id: 1)->generate(id: 1);
    $content = $response->content();

    expect($content)->toStartWith('RIFF')
        ->and(strlen($content))->toBeGreaterThan(44);
});

test('native talk helper applies preset scales before generating audio', function () {
    $presetStore = preset();
    $presetId = $presetStore->add([
        'id' => 99991,
        'name' => '統合テスト用プリセット',
        'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
        'style_id' => 1,
        'speedScale' => 1.4,
        'pitchScale' => 0.1,
        'intonationScale' => 1.2,
        'volumeScale' => 1.0,
        'prePhonemeLength' => 0.1,
        'postPhonemeLength' => 0.1,
    ]);

    try {
        $query = talk('プリセット統合テスト', id: 1, preset: $presetId);
        $content = $query->generate(id: 1)->content();

        expect($query->audioQuery['speedScale'])->toBe(1.4)
            ->and($content)->toStartWith('RIFF')
            ->and(strlen($content))->toBeGreaterThan(44);
    } finally {
        preset()->delete($presetId);
    }
});

test('engine audio_query and synthesis work with native core', function () {
    $audioQuery = $this->postJson('/audio_query', [
        'text' => '統合テストなのだ',
        'speaker' => 1,
    ])
        ->assertOk()
        ->json();

    expect($audioQuery)->toBeArray()->toHaveKey('speedScale');

    $response = $this->postJson('/synthesis?speaker=1', $audioQuery)
        ->assertOk()
        ->assertHeader('Content-Type', 'audio/wav');

    expect($response->getContent())->toStartWith('RIFF');
});

test('native audio feature renders frame ranges as pcm chunks', function () {
    $audioQuery = Synthesizer::createAudioQuery('ストリーミング音声合成の統合テスト', 1);
    $audioFeature = Synthesizer::createAudioFeature($audioQuery, 1);
    $frameRate = (new VoicevoxCore)->getFrameRate();
    $chunkFrames = (int) round(0.3 * $frameRate, 0, PHP_ROUND_HALF_EVEN);
    $totalFrames = $audioFeature->length();
    $chunks = [];

    for ($start = 0; $start < $totalFrames; $start += $chunkFrames) {
        $end = min($start + $chunkFrames, $totalFrames);
        $chunks[] = $audioFeature->slice($start, $end)->render();
    }

    expect($frameRate)->toBe(93.75);
    expect($totalFrames)->toBeGreaterThan($chunkFrames);
    expect(count($chunks))->toBeGreaterThan(1);

    foreach ($chunks as $chunk) {
        expect($chunk)->not->toBeEmpty();
        expect(strlen($chunk) % 2)->toBe(0);
    }
});
