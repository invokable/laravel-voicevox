<?php

declare(strict_types=1);

use Revolution\Voicevox\Voicevox;

test('import user dict', function () {
    Voicevox::expects('importUserDict')->with([], false);

    Voicevox::importUserDict([], false);
});

test('import user dict with override', function () {
    Voicevox::expects('importUserDict')->with(['word' => 'test'], true);

    Voicevox::importUserDict(['word' => 'test'], true);
});
