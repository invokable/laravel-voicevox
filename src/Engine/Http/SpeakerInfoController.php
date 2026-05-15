<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SpeakerInfoController
{
    public function __invoke(Request $request): JsonResponse
    {
        $uuid = $request->string('speaker_uuid')->value();
        $format = $request->string('resource_format', 'base64')->value();

        try {
            return response()->json(
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->speaker($uuid, $format),
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            );
        } catch (Throwable) {
            return response()->json([
                'error' => __(config('voicevox.engine.fallback_error')),
            ],
                status: Response::HTTP_NOT_IMPLEMENTED,
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );
        }
    }
}
