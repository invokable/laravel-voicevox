<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Traits\Tappable;
use Revolution\Voicevox\Voicevox;

class TalkAudioQuery
{
    use Tappable;
    use WithHttp;

    public function __construct(public array $audio_query)
    {
        //
    }

    /**
     * Generate talk.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function generate(int|string $id = 1, bool $upspeak = true, ?int $core_version = null): TalkResponse
    {
        $body = Voicevox::synthesis($this->audio_query, $id, $upspeak, $core_version);

        return new TalkResponse($body);
    }
}
