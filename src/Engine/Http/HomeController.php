<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\Request;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class HomeController
{
    public function __invoke(Request $request): Response
    {
        try {
            $html = Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->alive();

            return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
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
