<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use ZipArchive;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\spin;

class EngineInstallCommand extends Command
{
    protected $signature = 'voicevox:install';

    protected $description = 'Install resources files for voicevox engine.';

    public function handle(): void
    {
        File::deleteDirectories($this->characterInfoPath());

        $this->copyResources();

        spin(
            fn () => $this->cleanCharacterInfo(),
            'Cleaning character_info directory...',
        );

        $this->call('voicevox:filemap', ['dir' => $this->characterInfoPath()]);
    }

    /**
     * サブモジュールがあればコピー、なければGitHubからダウンロード。
     */
    protected function copyResources(): void
    {
        $from = __DIR__.'/../../voicevox_resource/character_info/';

        if (File::exists($from)) {
            File::copyDirectory($from, $this->characterInfoPath());

            $this->line('Copied resources/character_info from submodule.');
        } else {
            $this->downloadFromGitHub();
        }
    }

    protected function downloadFromGitHub(): void
    {
        if (! confirm('voicevox_resource is not found as a submodule. Download from GitHub? (400MB+)', default: true)) {
            $this->warn('Download cancelled.');

            return;
        }

        $url = 'https://github.com/VOICEVOX/voicevox_resource/archive/refs/heads/main.zip';
        $tmpZip = tempnam(sys_get_temp_dir(), 'voicevox_').'.zip';
        $tmpDir = sys_get_temp_dir().'/voicevox_extract_'.uniqid();

        try {
            $response = spin(
                fn () => Http::timeout(300)->withOptions(['sink' => $tmpZip])->get($url),
                'Downloading voicevox_resource from GitHub...',
            );

            if ($response->failed()) {
                $this->error('Failed to download voicevox_resource from GitHub.');

                return;
            }

            $zip = new ZipArchive;
            if ($zip->open($tmpZip) !== true) {
                $this->error('Failed to open downloaded zip file.');

                return;
            }

            $zip->extractTo($tmpDir);
            $zip->close();

            $extracted = collect(File::directories($tmpDir))->first();
            $characterInfoSrc = $extracted.'/character_info';

            if (! File::exists($characterInfoSrc)) {
                $this->error('character_info directory not found in downloaded archive.');

                return;
            }

            File::copyDirectory($characterInfoSrc, $this->characterInfoPath());

            $this->line('Downloaded and copied resources/character_info from GitHub.');
        } finally {
            File::delete($tmpZip);
            File::deleteDirectory($tmpDir);
        }
    }

    protected function cleanCharacterInfo(): void
    {
        if (! File::exists($this->characterInfoPath())) {
            return;
        }

        // ディレクトリ名から_以前の文字列を取り除いてUUID4部分だけにする
        foreach (File::directories($this->characterInfoPath()) as $dirPath) {
            $dirName = basename($dirPath);
            if (str_contains($dirName, '_')) {
                $newDirName = Str::afterLast($dirName, '_');
                File::move($dirPath, dirname($dirPath).'/'.$newDirName);
            }
        }

        // *.png_largeファイルを消去する
        foreach (File::allFiles($this->characterInfoPath()) as $file) {
            if (str_ends_with($file->getFilename(), '.png_large')) {
                File::delete($file->getPathname());
            }
        }
    }

    private function characterInfoPath(): string
    {
        return __DIR__.'/../../resources/character_info';
    }
}
