<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Engine\NativeUserDict;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UpdateUserDictWordController
{
    public function __invoke(Request $request, string $word_uuid): JsonResponse
    {
        $surface = $request->string('surface')->value();
        $pronunciation = $request->string('pronunciation')->value();
        $accentType = $request->integer('accent_type');
        $wordType = $request->string('word_type')->value() ?: null;
        $priority = $request->has('priority') ? $request->integer('priority') : null;

        try {
            app(NativeUserDict::class)->update($word_uuid, $surface, $pronunciation, $accentType, $wordType, $priority);

            return response()->json(null, Response::HTTP_NO_CONTENT);
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
