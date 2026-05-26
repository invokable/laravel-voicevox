<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeSpeakerController
{
    public function __invoke(Request $request): JsonResponse
    {
        // Speaker initialization is a no-op for the native core.
        // The core initializes all speakers at startup.
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
