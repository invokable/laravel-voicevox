<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstalledLibrariesController
{
    public function __invoke(Request $request): JsonResponse
    {
        // Library management is not supported in the native core.
        return response()->json([], options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
