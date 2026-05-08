<?php

declare(strict_types=1);

use Revolution\Voicevox\Voicevox;

test('manifest returns array', function () {
    Voicevox::expects('manifest')->andReturn(['name' => 'test-engine']);

    $manifest = Voicevox::manifest();

    expect($manifest)->toBeArray()->toHaveKey('name');
});
