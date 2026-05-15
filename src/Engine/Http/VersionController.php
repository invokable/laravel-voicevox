<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class VersionController
{
    public function __invoke(): JsonResponse
    {
        try {
            return response()->json(
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->version(),
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
