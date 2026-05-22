<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Revolution\Voicevox\Synthesizer;

use function Revolution\Voicevox\talk;

beforeEach(function () {
    $corePath = rtrim((string) config('voicevox.core.path', ''), '/');
    $coreLibPath = $corePath.'/c_api/lib/libvoicevox_core.so';

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

test('engine audio_query and synthesis work with native core', function () {
    $audioQuery = $this->postJson('/audio_query?text=統合テストなのだ&speaker=1')
        ->assertOk()
        ->json();

    expect($audioQuery)->toBeArray()->toHaveKey('speedScale');

    $response = $this->postJson('/synthesis?speaker=1', $audioQuery)
        ->assertOk()
        ->assertHeader('Content-Type', 'audio/wav');

    expect($response->getContent())->toStartWith('RIFF');
});
