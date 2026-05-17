<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Exceptions\ParseKanaError;
use Revolution\Voicevox\Support\KanaConverter;

class ValidateKanaController
{
    public function __invoke(Request $request): JsonResponse
    {
        $text = $request->string('text')->value();

        try {
            KanaConverter::parse($text);

            return response()->json(true);
        } catch (ParseKanaError $e) {
            return response()->json([
                'text' => $e->errorText,
                'error_name' => $e->errorName,
                'error_args' => $e->errorArgs,
            ], status: 400, options: JSON_UNESCAPED_UNICODE);
        }
    }
}
