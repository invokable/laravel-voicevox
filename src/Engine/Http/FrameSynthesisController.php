<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\Request;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FrameSynthesisController
{
    public function __invoke(Request $request): Response
    {
        $frameAudioQuery = $request->json()->all();
        $id = $request->integer('speaker', 3001);

        try {
            $audio = Synthesizer::frameSynthesis(json_encode($frameAudioQuery), $id);

            return response($audio, 200, ['Content-Type' => 'audio/wav']);
        } catch (Throwable) {
            // Fall back to Voicevox client if native core is unavailable
        }

        try {
            $audio = Voicevox::baseUrl(config('voicevox.engine.fallback_url'))
                ->frameSynthesis($frameAudioQuery, $id);

            return response($audio, 200, ['Content-Type' => 'audio/wav']);
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
