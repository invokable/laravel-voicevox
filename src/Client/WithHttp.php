<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

trait WithHttp
{
    protected function http(): PendingRequest
    {
        return Http::baseUrl(config('voicevox.client.url', 'http://127.0.0.1:50021'));
    }
}
