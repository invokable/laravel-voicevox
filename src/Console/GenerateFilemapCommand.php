<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateFilemapCommand extends Command
{
    protected $signature = 'voicevox:filemap
                            {dir? : target directory (default: resources/character_info)}
                            {--suffix=* : file suffixes to include (default: png wav)}';

    protected $description = 'Generate filemap.json for the ResourceManager.';

    /** @var list<string> */
    protected array $defaultSuffixes = ['png', 'wav'];

    public function handle(): int
    {
        $dir = $this->argument('dir') ?? __DIR__.'/../../resources/character_info';

        if (! File::isDirectory($dir)) {
            $this->error("{$dir} はディレクトリではありません");

            return self::FAILURE;
        }

        $suffixes = $this->option('suffix') ?: $this->defaultSuffixes;

        $pathToHash = $this->buildPathToHash($dir, $suffixes);

        $savePath = rtrim($dir, '/').'/filemap.json';
        File::put($savePath, json_encode($pathToHash, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $this->line('Generated filemap.json with '.count($pathToHash)." entries → {$savePath}");

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $suffixes
     * @return array<string, string>
     */
    protected function buildPathToHash(string $dir, array $suffixes): array
    {
        $pathToHash = [];

        foreach (File::allFiles($dir) as $file) {
            if (! in_array($file->getExtension(), $suffixes, strict: true)) {
                continue;
            }

            // Use forward-slash (POSIX) relative paths as keys for cross-platform compatibility.
            $relPath = str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());
            $pathToHash[$relPath] = hash('sha256', File::get($file->getPathname()));
        }

        ksort($pathToHash);

        return $pathToHash;
    }
}
