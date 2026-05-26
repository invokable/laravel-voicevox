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

class SingFrameVolumeController
{
    public function __invoke(Request $request): JsonResponse
    {
        $speaker = $request->integer('speaker', 6000);

        // Support both nested format {score: ..., frame_audio_query: ...}
        // and flat format (frame_audio_query as root body)
        if ($request->has('frame_audio_query')) {
            $score = $request->array('score');
            $score = Score::make($score['notes'] ?? [])->toArray();
            $frameAudioQuery = $request->array('frame_audio_query');
        } else {
            $score = Score::make([])->toArray();
            $frameAudioQuery = $request->json()->all();
        }

        try {
            return response()->json(
                json_decode(
                    Synthesizer::createSingFrameVolume(json_encode($score), json_encode($frameAudioQuery), $speaker),
                    true,
                ),
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (Throwable) {
            // Fall back to Voicevox client if native core is unavailable
        }

        try {
            return response()->json(
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->singFrameVolume($score, $frameAudioQuery, $speaker),
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
