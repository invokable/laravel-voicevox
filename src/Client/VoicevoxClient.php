<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Client;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Revolution\Voicevox\Client\Concerns\HasPreset;
use Revolution\Voicevox\Client\Concerns\HasSong;
use Revolution\Voicevox\Client\Concerns\HasTalk;
use Revolution\Voicevox\Client\Concerns\HasUserDict;
use Revolution\Voicevox\Client\Concerns\Unsupported;
use Revolution\Voicevox\Client\Concerns\WithHttp;

class VoicevoxClient
{
    use HasPreset;
    use HasSong;
    use HasTalk;
    use HasUserDict;
    use Unsupported;
    use WithHttp;

    /**
     * Create a talk.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function talk(string $text, int|string $id = 1, bool $enableKatakanaEnglish = true): TalkAudioQuery
    {
        $audioQuery = $this->audioQuery($text, $id, $enableKatakanaEnglish);

        return new TalkAudioQuery(audioQuery: $audioQuery, id: $id);
    }

    /**
     * Create a song.
     *
     * @param  int|string  $teacher  typeがsingかsinging_teacherのスタイルID
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function song(array|Arrayable $score, int|string $teacher = 6000): SongAudioQuery
    {
        $score = $score instanceof Arrayable ? $score->toArray() : $score;

        $frameAudioQuery = $this->singFrameAudioQuery($score, $teacher);

        return new SongAudioQuery(score: $score, frameAudioQuery: $frameAudioQuery, teacher: $teacher);
    }

    /**
     * Engine Version.
     */
    public function version(): string
    {
        return $this->http()->get('version')->body();
    }

    /**
     * Engine Manifest.
     */
    public function manifest(): array
    {
        return $this->http()->get('engine_manifest')->json();
    }

    /**
     * Validate text against AquesTalk notation.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function validateKana(string $text): bool
    {
        return $this->http()->withQueryParameters(['text' => $text])
            ->post('validate_kana')
            ->throw()
            ->json();
    }

    /**
     * List available core versions.
     */
    public function coreVersions(): array
    {
        return $this->http()->get('core_versions')->json();
    }
}
