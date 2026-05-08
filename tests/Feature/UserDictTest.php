<?php

declare(strict_types=1);

use Revolution\Voicevox\Voicevox;

test('user dict returns array', function () {
    Voicevox::expects('userDict')->andReturn(['uuid-1' => ['surface' => 'Laravel']]);

    $dict = Voicevox::userDict();

    expect($dict)->toBeArray()->toHaveKey('uuid-1');
});

test('add word returns uuid', function () {
    $uuid = 'a1b2c3d4-0000-0000-0000-000000000000';
    Voicevox::expects('addWord')->andReturn($uuid);

    $result = Voicevox::addWord('Laravel', 'ララベル', 3);

    expect($result)->toBe($uuid);
});

test('update word', function () {
    Voicevox::expects('updateWord')->with('uuid-1', 'Laravel', 'ララベル', 3);

    Voicevox::updateWord('uuid-1', 'Laravel', 'ララベル', 3);
});

test('delete word', function () {
    Voicevox::expects('deleteWord')->with('uuid-1');

    Voicevox::deleteWord('uuid-1');
});
