<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\Request;
use Revolution\Voicevox\Ai\Concerns\ResolvesVoiceId;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OpenAiSpeechController
{
    use ResolvesVoiceId;

    public function __invoke(Request $request): Response
    {
        $validated = $request->validate([
            'input' => ['required', 'string', 'max:4096'],
            'model' => ['nullable', 'string'],
            'voice' => ['required'],
            'voice.id' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string', 'max:4096'],
            'response_format' => ['nullable', 'string'],
            'speed' => ['nullable', 'numeric', 'between:0.25,4'],
            'stream_format' => ['nullable', 'string'],
        ]);

        $text = $validated['input'];
        $id = $this->resolveVoiceId($this->voice($validated['voice']));
        $speed = (float) ($validated['speed'] ?? 1.0);

        try {
            $audio = $this->synthesizeWithCore($text, $id, $speed);

            return response($audio, 200, ['Content-Type' => 'audio/wav']);
        } catch (Throwable) {
            // Fall back to Voicevox client if native core is unavailable
        }

        try {
            $audio = $this->synthesizeWithFallback($text, $id, $speed);

            return response($audio, 200, ['Content-Type' => 'audio/wav']);
        } catch (Throwable) {
            return response()->json([
                'error' => __(config('voicevox.engine.fallback_error')),
            ],
                status: Response::HTTP_NOT_IMPLEMENTED,
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            );
        }
    }

    private function synthesizeWithCore(string $text, int $id, float $speed): string
    {
        $audioQuery = json_decode(
            Synthesizer::createAudioQuery($text, $id),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $audioQuery['speedScale'] = $speed;

        return Synthesizer::synthesis(
            json_encode($audioQuery, JSON_THROW_ON_ERROR),
            $id,
            true,
        );
    }

    private function synthesizeWithFallback(string $text, int $id, float $speed): string
    {
        $voicevox = Voicevox::baseUrl(config('voicevox.engine.fallback_url'));
        $audioQuery = $voicevox->audioQuery($text, $id);
        $audioQuery['speedScale'] = $speed;

        return $voicevox->synthesis($audioQuery, $id);
    }

    private function voice(mixed $voice): int|string
    {
        if (is_array($voice)) {
            $voice = $voice['id'] ?? '';
        }

        if (is_int($voice) || is_string($voice)) {
            return $voice;
        }

        return '';
    }
}
