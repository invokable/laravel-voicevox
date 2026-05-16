<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine;

use Illuminate\Support\Facades\File;

/**
 * リソースファイルのパスと一意なハッシュ値の対応(filemap)を管理する。
 *
 * APIでリソースファイルを一意なURLとして返すときに使う。
 * ついでにファイルをbase64文字列に変換することもできる。
 */
class ResourceManager
{
    /** @var array<string, string> absolute path => hash */
    protected array $pathToHash = [];

    /** @var array<string, string> hash => absolute path */
    protected array $hashToPath = [];

    /**
     * @param  bool  $createFilemapIfNotExist  filemap.json がない場合でも登録時にハッシュを生成するか
     */
    public function __construct(protected bool $createFilemapIfNotExist = false)
    {
        //
    }

    /**
     * ディレクトリをfilemapに登録する。
     *
     * @throws ResourceManagerError
     */
    public function registerDir(string $resourceDir): void
    {
        $filemapJson = $resourceDir.'/filemap.json';

        if (File::exists($filemapJson)) {
            /** @var array<string, string> $data */
            $data = json_decode(File::get($filemapJson), associative: true);

            foreach ($data as $relPath => $hash) {
                $absPath = $resourceDir.'/'.str_replace('/', DIRECTORY_SEPARATOR, $relPath);
                $this->pathToHash[$absPath] = $hash;
            }
        } elseif ($this->createFilemapIfNotExist) {
            foreach (File::allFiles($resourceDir) as $file) {
                $absPath = $file->getPathname();
                $this->pathToHash[$absPath] = hash('sha256', File::get($absPath));
            }
        } else {
            throw new ResourceManagerError("{$filemapJson} が見つかりません");
        }

        $this->hashToPath = array_flip($this->pathToHash);
    }

    /**
     * 指定したリソースファイルのbase64文字列やハッシュ値を返す。
     *
     * @param  string  $format  'base64' または 'hash'
     *
     * @throws ResourceManagerError
     */
    public function resourceStr(string $resourcePath, string $format = 'base64'): string
    {
        $hash = $this->pathToHash[$resourcePath] ?? null;

        if ($hash === null) {
            throw new ResourceManagerError("{$resourcePath} がfilemapに登録されていません");
        }

        if ($format === 'base64') {
            return base64_encode(File::get($resourcePath));
        }

        return $hash;
    }

    /**
     * 指定したハッシュ値を持つリソースファイルの絶対パスを返す。
     *
     * @throws ResourceManagerError
     */
    public function resourcePath(string $hash): string
    {
        $path = $this->hashToPath[$hash] ?? null;

        if ($path === null) {
            throw new ResourceManagerError("'{$hash}' に対応するリソースがありません");
        }

        return $path;
    }
}
