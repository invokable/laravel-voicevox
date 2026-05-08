<?php

declare(strict_types=1);

namespace Revolution\Voicevox;

use Illuminate\Support\Facades\Facade;
use Revolution\Voicevox\Client\VoicevoxClient;

class Voicevox extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return VoicevoxClient::class;
    }
}
