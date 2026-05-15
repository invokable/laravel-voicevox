<?php

declare(strict_types=1);

use Revolution\Voicevox\Engine\MetaStore;

$sampleMetas = [
    [
        'name' => 'ずんだもん',
        'speaker_uuid' => '388f246b-8c41-4ac1-8e2d-5d79f3ff56d9',
        'version' => '0.16.0',
        'order' => 1,
        'styles' => [
            ['id' => 3, 'name' => 'ノーマル', 'type' => 'talk', 'order' => 0],
            ['id' => 3003, 'name' => 'ノーマル', 'type' => 'frame_decode', 'order' => 0],
            ['id' => 1, 'name' => 'あまあま', 'type' => 'talk', 'order' => 1],
            ['id' => 3001, 'name' => 'あまあま', 'type' => 'frame_decode', 'order' => 1],
        ],
    ],
    [
        'name' => '波音リツ',
        'speaker_uuid' => 'b1a81618-b27b-40d2-b0ea-27a9ad408c4b',
        'version' => '0.16.1',
        'order' => 4,
        'styles' => [
            ['id' => 6000, 'name' => 'ノーマル', 'type' => 'sing', 'order' => 0],
            ['id' => 3009, 'name' => 'ノーマル', 'type' => 'frame_decode', 'order' => 1],
        ],
    ],
    [
        'name' => '四国めたん',
        'speaker_uuid' => '7ffcb7ce-00ec-4bdc-82cd-45a8889e43ff',
        'version' => '0.16.0',
        'order' => 0,
        'styles' => [
            ['id' => 2, 'name' => 'ノーマル', 'type' => 'talk', 'order' => 0],
            ['id' => 3002, 'name' => 'ノーマル', 'type' => 'frame_decode', 'order' => 0],
        ],
    ],
];

test('all returns all characters', function () use ($sampleMetas) {
    $store = new MetaStore($sampleMetas);

    expect($store->all())->toHaveCount(3);
});

test('speakers returns only talk-type styles', function () use ($sampleMetas) {
    $store = new MetaStore($sampleMetas);
    $speakers = $store->speakers();

    // 波音リツ has no talk styles, should be excluded
    expect($speakers)->toHaveCount(2);

    $zundamon = collect($speakers)->firstWhere('name', 'ずんだもん');
    expect($zundamon)->not->toBeNull();
    expect($zundamon['styles'])->each(fn ($style) => $style->type->toBe('talk'));
    expect($zundamon['styles'])->toHaveCount(2);
});

test('singers returns only sing-type styles', function () use ($sampleMetas) {
    $store = new MetaStore($sampleMetas);
    $singers = $store->singers();

    // 波音リツ has sing + frame_decode → included
    // ずんだもん has frame_decode → included
    // 四国めたん has frame_decode → included
    expect($singers)->toHaveCount(3);

    $ritu = collect($singers)->firstWhere('name', '波音リツ');
    expect($ritu)->not->toBeNull();
    expect($ritu['styles'])->each(fn ($style) => $style->type->not->toBe('talk'));

    // ずんだもん: only frame_decode styles remain
    $zundamon = collect($singers)->firstWhere('name', 'ずんだもん');
    expect($zundamon['styles'])->toHaveCount(2);
    expect($zundamon['styles'])->each(fn ($style) => $style->type->toBe('frame_decode'));
});

test('speakers excludes characters with no talk styles', function () use ($sampleMetas) {
    $store = new MetaStore($sampleMetas);
    $speakers = $store->speakers();

    $names = collect($speakers)->pluck('name')->all();
    expect($names)->not->toContain('波音リツ');
});

test('MetaStore handles stdClass input from json_decode', function () use ($sampleMetas) {
    $json = json_encode($sampleMetas);
    $decoded = json_decode($json); // stdClass objects

    $store = new MetaStore($decoded);
    $speakers = $store->speakers();

    expect($speakers)->toHaveCount(2);
    expect($speakers[0]['styles'][0]['type'])->toBe('talk');
});
