<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Support\KanaConverter;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ValidateKanaController
{
    public function __invoke(Request $request): JsonResponse
    {
        $text = $request->string('text')->value();

        try {
            KanaConverter::parse($text);

            return response()->json(json_encode(true));
        } catch (Throwable) {

        }
    }
}
