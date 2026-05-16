<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->tmpDir = sys_get_temp_dir().'/voicevox_filemap_test_'.uniqid();
    File::makeDirectory($this->tmpDir.'/icons', recursive: true);
    File::makeDirectory($this->tmpDir.'/voice_samples', recursive: true);

    File::put($this->tmpDir.'/icons/style1.png', 'img1');
    File::put($this->tmpDir.'/icons/style2.png', 'img2');
    File::put($this->tmpDir.'/voice_samples/sample1.wav', 'wav1');
    File::put($this->tmpDir.'/voice_samples/sample2.wav', 'wav2');
    File::put($this->tmpDir.'/policy.md', 'text file');
});

afterEach(function () {
    File::deleteDirectory($this->tmpDir);
});

test('voicevox:filemap generates filemap.json with png and wav files', function () {
    $this->artisan('voicevox:filemap', ['dir' => $this->tmpDir])->assertSuccessful();

    expect(File::exists($this->tmpDir.'/filemap.json'))->toBeTrue();

    $map = json_decode(File::get($this->tmpDir.'/filemap.json'), true);

    expect($map)->toHaveCount(4);
    expect($map)->toHaveKeys([
        'icons/style1.png',
        'icons/style2.png',
        'voice_samples/sample1.wav',
        'voice_samples/sample2.wav',
    ]);

    // text files are not included
    expect($map)->not->toHaveKey('policy.md');
});

test('filemap values are sha256 hashes of the file content', function () {
    $this->artisan('voicevox:filemap', ['dir' => $this->tmpDir])->assertSuccessful();

    $map = json_decode(File::get($this->tmpDir.'/filemap.json'), true);

    expect($map['icons/style1.png'])->toBe(hash('sha256', 'img1'));
    expect($map['voice_samples/sample1.wav'])->toBe(hash('sha256', 'wav1'));
});

test('voicevox:filemap with custom suffix includes only those extensions', function () {
    $this->artisan('voicevox:filemap', [
        'dir' => $this->tmpDir,
        '--suffix' => ['png'],
    ])->assertSuccessful();

    $map = json_decode(File::get($this->tmpDir.'/filemap.json'), true);

    expect($map)->toHaveCount(2);
    expect($map)->toHaveKeys(['icons/style1.png', 'icons/style2.png']);
    expect($map)->not->toHaveKey('voice_samples/sample1.wav');
});

test('voicevox:filemap uses forward slashes in keys on all platforms', function () {
    $this->artisan('voicevox:filemap', ['dir' => $this->tmpDir])->assertSuccessful();

    $map = json_decode(File::get($this->tmpDir.'/filemap.json'), true);

    foreach (array_keys($map) as $key) {
        expect($key)->not->toContain('\\');
    }
});

test('voicevox:filemap returns error for non-existent directory', function () {
    $this->artisan('voicevox:filemap', ['dir' => '/nonexistent/path'])->assertFailed();
});
