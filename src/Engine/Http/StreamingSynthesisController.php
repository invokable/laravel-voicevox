<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Revolution\Voicevox\Talk\TalkAudioQuery;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StreamingSynthesisController
{
    public function __invoke(Request $request): Response
    {
        $params = Validator::make($request->query(), [
            'start_offset' => ['nullable', 'numeric', 'min:0'],
            'segment_length' => ['nullable', 'numeric', 'gt:0'],
        ])->validate();

        $audioQuery = $request->json()->all();
        $id = $request->integer('speaker', 1);
        $startOffset = (float) ($params['start_offset'] ?? 0);
        $segmentLength = (float) ($params['segment_length'] ?? 0.3);
        $enableInterrogativeUpspeak = $request->boolean('enable_interrogative_upspeak', true);

        try {
            return (new TalkAudioQuery($audioQuery))
                ->stream($id, $startOffset, $segmentLength, $enableInterrogativeUpspeak)
                ->toResponse($request);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'detail' => $e->getMessage(),
            ],
                status: Response::HTTP_BAD_REQUEST,
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            );
        } catch (Throwable) {
            // Fall back to Voicevox client if native core is unavailable
        }

        try {
            $audio = Voicevox::baseUrl(config('voicevox.engine.fallback_url'))
                ->streamingSynthesis($audioQuery, $id, $startOffset, $segmentLength, $enableInterrogativeUpspeak);

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
