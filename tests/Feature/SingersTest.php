<?php

declare(strict_types=1);

use Revolution\Voicevox\Voicevox;

test('singers returns array', function () {
    Voicevox::expects('singers')->andReturn([['name' => 'ずんだもん', 'styles' => []]]);

    $singers = Voicevox::singers();

    expect($singers)->toBeArray()->toHaveCount(1);
});

test('singer returns info array', function () {
    Voicevox::expects('singer')->andReturn(['policy' => '', 'portrait' => '']);

    $info = Voicevox::singer('388f246b-8c41-4ac1-8e2d-5d79f3ff56d9');

    expect($info)->toBeArray()->toHaveKey('policy');
});
