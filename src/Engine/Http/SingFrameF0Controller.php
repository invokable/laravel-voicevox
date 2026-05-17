<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SingFrameF0Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // Body is the frame_audio_query; score passed via query
        $frameAudioQuery = $request->json()->all();
        $speaker = $request->integer('speaker', 6000);

        try {
            // Score is embedded in frame_audio_query for the engine API
            $score = $frameAudioQuery['score'] ?? [];
            unset($frameAudioQuery['score']);

            return response()->json(
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->singFrameF0($score, $frameAudioQuery, $speaker),
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
