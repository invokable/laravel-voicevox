<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SingFrameF0Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $score = $request->array('score');
        $score = Score::make($score['notes'] ?? [])->toArray();
        $speaker = $request->integer('speaker', 6000);
        $frameAudioQuery = $request->array('frame_audio_query');

        try {
            return response()->json(
                json_decode(
                    Synthesizer::createSingFrameF0(json_encode($score), json_encode($frameAudioQuery), $speaker),
                    true,
                ),
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (Throwable) {
            // Fall back to Voicevox client if native core is unavailable
        }

        try {
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
