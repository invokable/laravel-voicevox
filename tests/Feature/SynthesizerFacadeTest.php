<?php

declare(strict_types=1);

use Revolution\Voicevox\Core\Synthesizer as CoreSynthesizer;
use Revolution\Voicevox\Synthesizer;

test('Synthesizer facade class exists', function () {
    expect(class_exists(Synthesizer::class))->toBeTrue();
});

test('Synthesizer facade accessor is CoreSynthesizer', function () {
    $reflection = new ReflectionMethod(Synthesizer::class, 'getFacadeAccessor');
    $reflection->setAccessible(true);

    expect($reflection->invoke(null))->toBe(CoreSynthesizer::class);
});
