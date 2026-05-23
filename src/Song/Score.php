<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Song;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;

class Score implements Arrayable, Jsonable
{
    /**
     * 楽譜情報。
     *
     * @param  array<Note|array>  $notes  音符のリスト。1音目は必ず休符。
     */
    public function __construct(
        public array $notes,
    ) {
        // ConvertEmptyStringsToNullミドルウェアによってlyricがnullになることがあるのでここで対処
        $this->notes = Collection::make($this->notes)
            ->map(function ($note) {
                return $note instanceof Note ? $note : Note::make(length: $note['length'] ?? 100, lyric: $note['lyric'] ?? '', key: $note['key'] ?? null, id: $note['id'] ?? null);
            })
            ->toArray();
    }

    public static function make(array $notes): self
    {
        return new self($notes);
    }

    public function add(Note $note): self
    {
        $this->notes[] = $note;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'notes' => Collection::make($this->notes)->toArray(),
        ];
    }

    public function toJson($options = 0): string
    {
        return Collection::make($this->toArray())->toJson($options);
    }
}
