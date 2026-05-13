<?php

declare(strict_types=1);

use Revolution\Voicevox\Voicevox;
use Revolution\Voicevox\VoicevoxResponse;

test('VOICEVOX', function () {
    Voicevox::expects('talk->generate')->andReturn(new VoicevoxResponse('test'));

    $response = Voicevox::talk('test')->generate(id: 1);

    expect($response->content())->toBe('test');
});
