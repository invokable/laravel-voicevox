<?php

declare(strict_types=1);

use Revolution\Voicevox\Client\VoiceResponse;
use Revolution\Voicevox\Voicevox;

test('VOICEVOX', function () {
    Voicevox::expects('voice->generate')->andReturn(new VoiceResponse('test'));

    $response = Voicevox::voice('test')->generate();

    expect($response->content())->toBe('test');
});
