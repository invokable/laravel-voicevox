<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UninstallLibraryController
{
    public function __invoke(Request $request, string $library_uuid): JsonResponse
    {
        // Library management is not supported in the native core.
        return response()->json();
    }
}
