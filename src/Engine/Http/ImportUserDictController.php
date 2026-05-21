<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Engine\NativeUserDict;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ImportUserDictController
{
    public function __invoke(Request $request): JsonResponse
    {
        $override = $request->boolean('override', false);
        $words = $request->json()->all();

        try {
            app(NativeUserDict::class)->import(json_encode($words), $override);

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
