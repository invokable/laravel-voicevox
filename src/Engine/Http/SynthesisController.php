<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\Request;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SynthesisController
{
    public function __invoke(Request $request): Response
    {
        $audioQuery = $request->json()->all();
        $id = $request->integer('speaker', 1);
        $enableInterrogativeUpspeak = $request->boolean('enable_interrogative_upspeak', true);

        try {
            $audio = Voicevox::baseUrl(config('voicevox.engine.fallback_url'))
                ->synthesis($audioQuery, $id, $enableInterrogativeUpspeak);

            return response($audio, 200, ['Content-Type' => 'audio/wav']);
        } catch (Throwable) {
            return response()->json([
                'error' => __(config('voicevox.engine.fallback_error')),
            ], Response::HTTP_NOT_IMPLEMENTED, options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
    }
}
