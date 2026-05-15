<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SingersController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            return response()->json(
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->singers(),
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (Throwable) {
            return response()->json([
                'error' => __(config('voicevox.engine.fallback_error')),
            ], Response::HTTP_NOT_IMPLEMENTED, options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
    }
}
