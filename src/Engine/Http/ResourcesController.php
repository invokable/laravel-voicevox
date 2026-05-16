<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Revolution\Voicevox\Engine\ResourceManager;
use Revolution\Voicevox\Engine\ResourceManagerError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResourcesController
{
    public function __construct(
        protected ?string $characterInfoPath = null,
    ) {}

    public function __invoke(Request $request, string $hash): BinaryFileResponse
    {
        $rm = new ResourceManager(createFilemapIfNotExist: true);
        $dir = $this->characterInfoPath ?? dirname(__DIR__, 3).'/resources/character_info';

        if (File::isDirectory($dir)) {
            $rm->registerDir($dir);
        }

        try {
            return response()->file($rm->resourcePath($hash));
        } catch (ResourceManagerError) {
            abort(404);
        }
    }
}
