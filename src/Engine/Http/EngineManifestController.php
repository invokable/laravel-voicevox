<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EngineManifestController
{
    public function __invoke(Request $request): JsonResponse
    {
        $manifest = File::json(dirname(__DIR__, 3).'/resources/engine_manifest.json');

        return response()->json(
            $manifest,
            options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }
}
