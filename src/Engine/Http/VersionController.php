<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Revolution\Voicevox\Enums\Engine;

class VersionController
{
    public function __invoke(): JsonResponse
    {
        return response()->json(Engine::Version->value);
    }
}
