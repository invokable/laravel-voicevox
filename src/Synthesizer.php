<?php

declare(strict_types=1);

namespace Revolution\Voicevox;

use Illuminate\Support\Facades\Facade;
use Revolution\Voicevox\Core\Synthesizer as CoreSynthesizer;

class Synthesizer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CoreSynthesizer::class;
    }
}
