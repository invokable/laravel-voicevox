<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Engine\NativePresetStore;
use Revolution\Voicevox\Talk\Talk;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AudioQueryFromPresetController
{
    public function __invoke(Request $request, NativePresetStore $store): JsonResponse
    {
        $text = $request->string('text')->value();
        $presetId = $request->integer('preset_id');
        // $enableKatakanaEnglish = $request->boolean('enable_katakana_english', true);

        try {
            $audioQuery = app(Talk::class)->preset($text, $presetId)->audioQuery;

            return response()->json(
                $audioQuery,
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
