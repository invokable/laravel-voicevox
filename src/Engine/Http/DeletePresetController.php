<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Engine\NativePresetStore;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DeletePresetController
{
    public function __invoke(Request $request, NativePresetStore $store): JsonResponse
    {
        $id = $request->integer('id');

        try {
            $store->delete($id);

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Throwable) {
            // Fall back to official engine
        }

        try {
            Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->deletePreset($id);

            return response()->json(null);
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
