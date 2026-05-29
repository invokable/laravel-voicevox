<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Engine\NativeUserDict;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DeleteUserDictWordController
{
    public function __invoke(Request $request, NativeUserDict $dict, string $word_uuid): JsonResponse
    {
        try {
            $dict->delete($word_uuid);

            return response()->json(null, Response::HTTP_NO_CONTENT);
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
