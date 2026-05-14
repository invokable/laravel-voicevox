<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        try {
            return response()->json(
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->audioQuery($text, $id, $enable_katakana_english),
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            );
        } catch (Throwable) {
            return response(status: Response::HTTP_NOT_IMPLEMENTED)->json([
                'error' => __(config('voicevox.engine.fallback_error')),
            ], options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
    }
}
