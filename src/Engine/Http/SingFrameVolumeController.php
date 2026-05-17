<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SingFrameVolumeController
{
    public function __invoke(Request $request): JsonResponse
    {
        $score = $request->array('score');
        $frameAudioQuery = $request->array('frame_audio_query');
        $speaker = $request->integer('speaker', 6000);

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
