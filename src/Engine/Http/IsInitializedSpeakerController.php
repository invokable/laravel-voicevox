<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class IsInitializedSpeakerController
{
    public function __invoke(Request $request): JsonResponse
    {
        $id = $request->integer('speaker', 1);

        try {
            $result = Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->isInitializedSpeaker($id);

            return response()->json($result, options: JSON_PRETTY_PRINT);
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
