<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
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

test('all includes default supported_features when no character info path given', function () use ($sampleMetas) {
    $store = new MetaStore($sampleMetas);
    $all = $store->all();

    foreach ($all as $character) {
        expect($character)->toHaveKey('supported_features');
        expect($character['supported_features'])->toHaveKey('permitted_synthesis_morphing');
        expect($character['supported_features']['permitted_synthesis_morphing'])->toBe('ALL');
    }
});

test('supported_features loaded from metas.json in character_info directory', function () use ($sampleMetas) {
    $tempDir = sys_get_temp_dir().'/metastore_test_'.uniqid();
    $uuid = '388f246b-8c41-4ac1-8e2d-5d79f3ff56d9';
    $charDir = $tempDir.'/'.$uuid;

    File::makeDirectory($charDir, recursive: true);
    File::put($charDir.'/metas.json', json_encode([
        'supported_features' => ['permitted_synthesis_morphing' => 'SELF_ONLY'],
    ]));

    $store = new MetaStore($sampleMetas, $tempDir);
    $speakers = $store->speakers();

    $zundamon = collect($speakers)->firstWhere('name', 'ずんだもん');
    expect($zundamon['supported_features']['permitted_synthesis_morphing'])->toBe('SELF_ONLY');

    File::deleteDirectory($tempDir);
});

test('speaker returns character info with policy, portrait, and style_infos', function () use ($sampleMetas) {
    $uuid = '388f246b-8c41-4ac1-8e2d-5d79f3ff56d9';
    $tempDir = sys_get_temp_dir().'/metastore_test_'.uniqid();
    $charDir = $tempDir.'/'.$uuid;

    File::makeDirectory($charDir.'/icons', recursive: true);
    File::makeDirectory($charDir.'/voice_samples', recursive: true);
    File::put($charDir.'/policy.md', 'Test policy');
    File::put($charDir.'/portrait.png', 'portrait-data');
    File::put($charDir.'/metas.json', json_encode(['supported_features' => ['permitted_synthesis_morphing' => 'ALL']]));

    // talk styles: id=3, id=1
    foreach ([3, 1] as $id) {
        File::put($charDir.'/icons/'.$id.'.png', 'icon-'.$id);
        for ($j = 1; $j <= 3; $j++) {
            $num = str_pad((string) $j, 3, '0', STR_PAD_LEFT);
            File::put($charDir.'/voice_samples/'.$id.'_'.$num.'.wav', "wav-{$id}-{$num}");
        }
    }

    $store = new MetaStore($sampleMetas, $tempDir);
    $info = $store->speaker($uuid);

    expect($info)->toHaveKeys(['policy', 'portrait', 'style_infos']);
    expect($info['policy'])->toBe('Test policy');
    expect($info['portrait'])->toBe(base64_encode('portrait-data'));
    expect($info['style_infos'])->toHaveCount(2);

    $first = $info['style_infos'][0];
    expect($first)->toHaveKeys(['id', 'icon', 'portrait', 'voice_samples']);
    expect($first['id'])->toBe(3);
    expect($first['icon'])->toBe(base64_encode('icon-3'));
    expect($first['portrait'])->toBeNull();
    expect($first['voice_samples'])->toHaveCount(3);
    expect($first['voice_samples'][0])->toBe(base64_encode('wav-3-001'));

    File::deleteDirectory($tempDir);
});

test('singer returns character info for sing-type character', function () use ($sampleMetas) {
    $uuid = 'b1a81618-b27b-40d2-b0ea-27a9ad408c4b'; // 波音リツ, styles: sing 6000, frame_decode 3009
    $tempDir = sys_get_temp_dir().'/metastore_test_'.uniqid();
    $charDir = $tempDir.'/'.$uuid;

    File::makeDirectory($charDir.'/icons', recursive: true);
    File::makeDirectory($charDir.'/voice_samples', recursive: true);
    File::put($charDir.'/policy.md', 'Singer policy');
    File::put($charDir.'/portrait.png', 'portrait-singer');
    File::put($charDir.'/metas.json', json_encode(['supported_features' => ['permitted_synthesis_morphing' => 'ALL']]));

    foreach ([6000, 3009] as $id) {
        File::put($charDir.'/icons/'.$id.'.png', 'icon-'.$id);
        for ($j = 1; $j <= 3; $j++) {
            $num = str_pad((string) $j, 3, '0', STR_PAD_LEFT);
            File::put($charDir.'/voice_samples/'.$id.'_'.$num.'.wav', "wav-{$id}-{$num}");
        }
    }

    $store = new MetaStore($sampleMetas, $tempDir);
    $info = $store->singer($uuid);

    expect($info['policy'])->toBe('Singer policy');
    expect($info['style_infos'])->toHaveCount(2);
    expect($info['style_infos'][0]['id'])->toBe(6000);

    File::deleteDirectory($tempDir);
});

test('speaker throws when UUID not found', function () use ($sampleMetas) {
    $tempDir = sys_get_temp_dir().'/metastore_test_'.uniqid();
    File::makeDirectory($tempDir);

    $store = new MetaStore($sampleMetas, $tempDir);

    expect(fn () => $store->speaker('non-existent-uuid'))->toThrow(RuntimeException::class);

    File::deleteDirectory($tempDir);
});

test('singer throws when UUID not found in singers list', function () use ($sampleMetas) {
    $tempDir = sys_get_temp_dir().'/metastore_test_'.uniqid();
    File::makeDirectory($tempDir);

    $store = new MetaStore($sampleMetas, $tempDir);

    // 四国めたん has no sing-type style (only frame_decode which IS sing-type, wait…)
    // 四国めたん (7ffcb7ce) has frame_decode → is in singers list, need a truly absent UUID
    expect(fn () => $store->singer('00000000-0000-0000-0000-000000000000'))->toThrow(RuntimeException::class);

    File::deleteDirectory($tempDir);
});

test('speaker returns URL format with hash when format is url', function () use ($sampleMetas) {
    $uuid = '388f246b-8c41-4ac1-8e2d-5d79f3ff56d9';
    $tempDir = sys_get_temp_dir().'/metastore_test_'.uniqid();
    $charDir = $tempDir.'/'.$uuid;

    File::makeDirectory($charDir.'/icons', recursive: true);
    File::makeDirectory($charDir.'/voice_samples', recursive: true);
    File::put($charDir.'/policy.md', 'Test policy');
    File::put($charDir.'/portrait.png', 'portrait-data');
    File::put($charDir.'/metas.json', json_encode(['supported_features' => ['permitted_synthesis_morphing' => 'ALL']]));

    foreach ([3, 1] as $id) {
        File::put($charDir.'/icons/'.$id.'.png', 'icon-'.$id);
        for ($j = 1; $j <= 3; $j++) {
            $num = str_pad((string) $j, 3, '0', STR_PAD_LEFT);
            File::put($charDir.'/voice_samples/'.$id.'_'.$num.'.wav', "wav-{$id}-{$num}");
        }
    }

    $store = new MetaStore($sampleMetas, $tempDir);
    $info = $store->speaker($uuid, 'url');

    $expectedPortraitHash = hash('sha256', 'portrait-data');
    $expectedIconHash = hash('sha256', 'icon-3');
    $expectedSampleHash = hash('sha256', 'wav-3-001');

    expect($info['portrait'])->toContain('/_resources/'.$expectedPortraitHash);
    expect($info['style_infos'][0]['icon'])->toContain('/_resources/'.$expectedIconHash);
    expect($info['style_infos'][0]['voice_samples'][0])->toContain('/_resources/'.$expectedSampleHash);

    File::deleteDirectory($tempDir);
});
