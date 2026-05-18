<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Revolution\Voicevox\Engine\NativePresetStore;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AudioQueryFromPresetController
{
    /**
     * Preset scale fields that override the AudioQuery defaults.
     */
    private const PRESET_SCALES = [
        'speedScale',
        'pitchScale',
        'intonationScale',
        'volumeScale',
        'prePhonemeLength',
        'postPhonemeLength',
        'pauseLength',
        'pauseLengthScale',
    ];

    public function __invoke(Request $request, NativePresetStore $store): JsonResponse
    {
        $text = $request->string('text')->value();
        $presetId = $request->integer('preset_id');
        $enableKatakanaEnglish = $request->boolean('enable_katakana_english', true);

        // --- Native path: local preset store + core Synthesizer ---
        try {
            $preset = $store->find($presetId);

            if ($preset !== null) {
                $styleId = (int) $preset['style_id'];

                $audioQuery = json_decode(Synthesizer::createAudioQuery($text, $styleId), true);

                // Apply preset-level scale overrides
                foreach (self::PRESET_SCALES as $key) {
                    if (isset($preset[$key])) {
                        $audioQuery[$key] = $preset[$key];
                    }
                }

                return response()->json(
                    $audioQuery,
                    options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                );
            }
        } catch (Throwable) {
            // Fall through to official engine
        }

        // --- Fallback: official engine ---
        try {
            $audioQuery = Voicevox::baseUrl(config('voicevox.engine.fallback_url'))->audioQueryFromPreset($text, $presetId, $enableKatakanaEnglish);

            return response()->json(
                $audioQuery,
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
