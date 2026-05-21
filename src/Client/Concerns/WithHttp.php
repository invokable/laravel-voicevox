<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client\Concerns;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

trait WithHttp
{
    protected ?string $base_url = null;

    public function baseUrl(?string $base_url = null): self
    {
        $this->base_url = $base_url;

        return $this;
    }

    protected function http(): PendingRequest
    {
        return Http::baseUrl($this->base_url ?? config('voicevox.client.url', 'http://127.0.0.1:50021'));
    }
}
