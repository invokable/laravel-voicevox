<?php

namespace Revolution\Voicevox\Client;

use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class VoiceResponse
{
    public function __construct(protected string $body)
    {
        //
    }

    public function content(): string
    {
        return $this->body;
    }

    public function storeAs(string $path, ?string $name = null, ?string $disk = null, array $options = []): string|bool
    {
        if (is_null($name)) {
            [$path, $name] = ['', $path];
        }

        $result = Container::getInstance()->make(FilesystemFactory::class)->disk($disk)->put(
            $path = trim($path.'/'.$name, '/'), $this->content(), $options,
        );

        return $result ? $path : false;
    }

    public function __toString(): string
    {
        return $this->content();
    }
}
