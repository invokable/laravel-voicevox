<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Engine\MetaStore;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SingerInfoController
{
    public function __invoke(Request $request): JsonResponse
    {
        $uuid = $request->string('speaker_uuid')->value();
        $format = $request->string('resource_format', 'base64')->value();

        try {
            $info = MetaStore::make(json_decode(Synthesizer::metas(), true))->singer($uuid);

            return response()->json(
                $info,
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            );
        } catch (Throwable) {
            // Fall back to Voicevox client if native core or character_info is unavailable
        }

        try {
            return response()->json(
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->singer($uuid, $format),
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
