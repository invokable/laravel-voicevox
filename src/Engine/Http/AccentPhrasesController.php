<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AccentPhrasesController
{
    public function __invoke(Request $request): JsonResponse
    {
        $text = $request->string('text')->value();
        $id = $request->integer('speaker');
        $isKana = $request->boolean('is_kana', false);
        $katakanaEnglish = $request->boolean('enable_katakana_english', true);

        // enable_katakana_englishには非対応
        try {
            if ($isKana) {
                $accent_phrases = json_decode(Synthesizer::createAccentPhrasesFromKana($text, $id));
            } else {
                // is_kana=false時はAudioQueryを作ってからaccent_phrasesを抽出
                $audio_query = json_decode(Synthesizer::createAudioQuery($text, $id), true);
                $accent_phrases = $audio_query['accent_phrases'];
            }

            return response()->json(
                $accent_phrases,
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (Throwable) {
            // Fall back to Voicevox client if native core is unavailable
        }

        try {
            return response()->json(
                Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->accentPhrases($text, $id, $isKana, $katakanaEnglish),
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
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
