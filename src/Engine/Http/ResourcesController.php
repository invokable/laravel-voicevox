<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\Request;
use Revolution\Voicevox\Engine\ResourceManager;
use Revolution\Voicevox\Engine\ResourceManagerError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResourcesController
{
    public function __invoke(Request $request, string $hash): BinaryFileResponse
    {
        $rm = new ResourceManager(createFilemapIfNotExist: true);
        $rm->registerDir(dirname(__DIR__, 3).'/resources/character_info');

        try {
            return response()->file($rm->resourcePath($hash));
        } catch (ResourceManagerError) {
            abort(404);
        }
    }
}
