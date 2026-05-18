<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Engine\NativeUserDict;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UserDictController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            return response()->json(
                (new NativeUserDict())->toArray(),
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (Throwable) {
            // Fall back to official engine
        }

        try {
            return response()->json(
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->userDict(),
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (Throwable) {
            return response()->json([
                'error' => __(config('voicevox.engine.fallback_error')),
            ],
                status: Response::HTTP_NOT_IMPLEMENTED,
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            );
        }
    }
}
