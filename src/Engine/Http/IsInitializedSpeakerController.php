<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IsInitializedSpeakerController
{
    public function __invoke(Request $request): JsonResponse
    {
        // For the native core, speakers are always initialized after startup.
        return response()->json(true, options: JSON_PRETTY_PRINT);
    }
}
