<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AudioQueryController
{
    public function __invoke(Request $request): JsonResponse
    {
        $text = $request->string('text')->value();
        $id = $request->integer('speaker');
        $enable_katakana_english = $request->boolean('enable_katakana_english', true);

        // enable_katakana_englishには非対応
        try {
            $audio_query = Synthesizer::createAudioQuery($text, $id);

            return response()->json(
                json_decode($audio_query),
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (Throwable) {
            // Fall back to Voicevox client if native core is unavailable
        }

        try {
            return response()->json(
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->audioQuery($text, $id, $enable_katakana_english),
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
