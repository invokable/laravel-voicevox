<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Core\VoicevoxCore;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CoreVersionsController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            return response()->json(
                [app(VoicevoxCore::class)->getVersion()],
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
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
