<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Revolution\Voicevox\Engine\MetaStore;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SpeakersController
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $speakers = Cache::remember('engine.speakers', now()->plus(hours: 12), function () {
                return MetaStore::make(json_decode(Synthesizer::metas(), true))->speakers();
            });

            return response()->json(
                $speakers,
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (Throwable) {
            // Fall back to Voicevox client if native core is unavailable
        }

        try {
            return response()->json(
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->speakers(),
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
