<?php

declare(strict_types=1);

use Revolution\Voicevox\Client\TalkResponse;
use Revolution\Voicevox\Voicevox;

test('VOICEVOX', function () {
    Voicevox::expects('talk->generate')->andReturn(new TalkResponse('test'));

    $response = Voicevox::talk('test')->generate(id: 1);

    expect($response->content())->toBe('test');
});
