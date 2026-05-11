<?php

declare(strict_types=1);

use Revolution\Voicevox\Voicevox;

test('accentPhrases returns array', function () {
    $phrases = [['moras' => [], 'accent' => 1]];
    Voicevox::expects('accentPhrases')->andReturn($phrases);

    $result = Voicevox::accentPhrases('こんにちは', 1);

    expect($result)->toBeArray()->toHaveCount(1);
});

test('moraData returns array', function () {
    $phrases = [['moras' => [], 'accent' => 1]];
    Voicevox::expects('moraData')->andReturn($phrases);

    $result = Voicevox::moraData($phrases, 1);

    expect($result)->toBeArray();
});

test('moraLength returns array', function () {
    $phrases = [['moras' => [], 'accent' => 1]];
    Voicevox::expects('moraLength')->andReturn($phrases);

    $result = Voicevox::moraLength($phrases, 1);

    expect($result)->toBeArray();
});

test('moraPitch returns array', function () {
    $phrases = [['moras' => [], 'accent' => 1]];
    Voicevox::expects('moraPitch')->andReturn($phrases);

    $result = Voicevox::moraPitch($phrases, 1);

    expect($result)->toBeArray();
});
