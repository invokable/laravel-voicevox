<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\Request;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class MultiSynthesisController
{
    public function __invoke(Request $request): Response
    {
        $audioQueries = $request->json()->all();
        $id = $request->integer('speaker', 1);
        $interrogativeUpspeak = $request->boolean('enable_interrogative_upspeak', false);

        try {
            $response = Voicevox::baseUrl(config('voicevox.engine.fallback_url'))
                ->multiSynthesis($audioQueries, $id, $interrogativeUpspeak);

            return response($response->content(), 200, ['Content-Type' => 'audio/wav']);
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
