<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SettingController
{
    public function __invoke(Request $request): Response
    {
        try {
            if ($request->isMethod('POST')) {
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->updateSetting($request->json()->all());

                return response()->json(null, Response::HTTP_NO_CONTENT);
            }

            return response(Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->setting());
        } catch (Throwable $e) {
            return response()->json([
                'error' => __(config('voicevox.engine.fallback_error')),
            ],
                status: Response::HTTP_NOT_IMPLEMENTED,
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            );
        }
    }
}
