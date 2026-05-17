<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Revolution\Voicevox\Engine\ResourceManager;
use Revolution\Voicevox\Exceptions\ResourceManagerError;

beforeEach(function () {
    $this->tmpDir = sys_get_temp_dir().'/voicevox_rm_test_'.uniqid();
    File::makeDirectory($this->tmpDir, recursive: true);
});

afterEach(function () {
    File::deleteDirectory($this->tmpDir);
});

test('registerDir loads filemap.json when present', function () {
    $content = 'hello';
    File::put($this->tmpDir.'/sample.png', $content);

    $hash = hash('sha256', $content);
    File::put($this->tmpDir.'/filemap.json', json_encode(['sample.png' => $hash]));

    $manager = new ResourceManager;
    $manager->registerDir($this->tmpDir);

    $path = $this->tmpDir.'/sample.png';
    expect($manager->resourceStr($path, 'hash'))->toBe($hash);
});

test('registerStr returns hash from registered file', function () {
    $content = 'test content';
    $hash = hash('sha256', $content);
    File::put($this->tmpDir.'/icon.png', $content);
    File::put($this->tmpDir.'/filemap.json', json_encode(['icon.png' => $hash]));

    $manager = new ResourceManager;
    $manager->registerDir($this->tmpDir);

    expect($manager->resourceStr($this->tmpDir.'/icon.png', 'hash'))->toBe($hash);
});

test('resourceStr returns base64 of file content', function () {
    $content = 'binary-data';
    File::put($this->tmpDir.'/voice.wav', $content);
    $hash = hash('sha256', $content);
    File::put($this->tmpDir.'/filemap.json', json_encode(['voice.wav' => $hash]));

    $manager = new ResourceManager;
    $manager->registerDir($this->tmpDir);

    expect($manager->resourceStr($this->tmpDir.'/voice.wav', 'base64'))
        ->toBe(base64_encode($content));
});

test('resourcePath reverse-looks up path from hash', function () {
    $content = 'data';
    $hash = hash('sha256', $content);
    File::put($this->tmpDir.'/icon.png', $content);
    File::put($this->tmpDir.'/filemap.json', json_encode(['icon.png' => $hash]));

    $manager = new ResourceManager;
    $manager->registerDir($this->tmpDir);

    expect($manager->resourcePath($hash))->toBe($this->tmpDir.'/icon.png');
});

test('registerDir without filemap generates hashes when createFilemapIfNotExist is true', function () {
    $content = 'dynamic';
    File::put($this->tmpDir.'/sample.wav', $content);

    $manager = new ResourceManager(createFilemapIfNotExist: true);
    $manager->registerDir($this->tmpDir);

    $path = $this->tmpDir.'/sample.wav';
    $hash = $manager->resourceStr($path, 'hash');
    expect($hash)->toBe(hash('sha256', $content));
});

test('registerDir throws when filemap missing and createFilemapIfNotExist is false', function () {
    $manager = new ResourceManager(createFilemapIfNotExist: false);

    expect(fn () => $manager->registerDir($this->tmpDir))
        ->toThrow(ResourceManagerError::class);
});

test('resourceStr throws for unregistered path', function () {
    File::put($this->tmpDir.'/filemap.json', json_encode([]));

    $manager = new ResourceManager;
    $manager->registerDir($this->tmpDir);

    expect(fn () => $manager->resourceStr($this->tmpDir.'/nonexistent.png', 'hash'))
        ->toThrow(ResourceManagerError::class);
});

test('resourcePath throws for unknown hash', function () {
    File::put($this->tmpDir.'/filemap.json', json_encode([]));

    $manager = new ResourceManager;
    $manager->registerDir($this->tmpDir);

    expect(fn () => $manager->resourcePath('deadbeef'))
        ->toThrow(ResourceManagerError::class);
});

test('registerDir handles nested subdirectory paths in filemap', function () {
    File::makeDirectory($this->tmpDir.'/icons', recursive: true);
    $content = 'nested';
    File::put($this->tmpDir.'/icons/style.png', $content);
    $hash = hash('sha256', $content);
    File::put($this->tmpDir.'/filemap.json', json_encode(['icons/style.png' => $hash]));

    $manager = new ResourceManager;
    $manager->registerDir($this->tmpDir);

    $path = $this->tmpDir.'/icons'.DIRECTORY_SEPARATOR.'style.png';
    expect($manager->resourceStr($path, 'hash'))->toBe($hash);
});
