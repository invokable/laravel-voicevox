<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\Request;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Voicevox;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class MultiSynthesisController
{
    public function __invoke(Request $request): Response
    {
        $audioQueries = $request->json()->all();
        $id = $request->integer('speaker', 1);
        $interrogativeUpspeak = $request->boolean('enable_interrogative_upspeak', false);

        try {
            return $this->synthesizeNative($audioQueries, $id, $interrogativeUpspeak);
        } catch (Throwable) {
            // Fall back to Voicevox client if native core is unavailable
        }

        try {
            $response = Voicevox::baseUrl(config('voicevox.engine.fallback_url'))
                ->multiSynthesis($audioQueries, $id, $interrogativeUpspeak);

            return response($response->content(), 200, ['Content-Type' => 'application/zip']);
        } catch (Throwable) {
            return response()->json([
                'error' => __(config('voicevox.engine.fallback_error')),
            ],
                status: Response::HTTP_NOT_IMPLEMENTED,
                options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function synthesizeNative(array $audioQueries, int $id, bool $interrogativeUpspeak): Response
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'voicevox_multi_');

        if ($tmpFile === false) {
            throw new \RuntimeException('Failed to create temporary file.');
        }

        try {
            $zip = new \ZipArchive;

            if ($zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Failed to open zip archive.');
            }

            foreach ($audioQueries as $i => $entry) {
                $query = $entry['query'] ?? $entry;
                $speaker = $entry['speaker'] ?? $id;

                $audio = Synthesizer::synthesis(json_encode($query), $speaker, $interrogativeUpspeak);
                $zip->addFromString(sprintf('%03d.wav', $i), $audio);
            }

            $zip->close();

            $content = file_get_contents($tmpFile);

            if ($content === false) {
                throw new \RuntimeException('Failed to read temporary file.');
            }

            return response($content, 200, ['Content-Type' => 'application/zip']);
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}
