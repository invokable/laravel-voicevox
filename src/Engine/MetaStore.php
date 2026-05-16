<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MetaStore
{
    protected const array TALK_STYLE_TYPES = ['talk'];

    protected const array SING_STYLE_TYPES = ['singing_teacher', 'frame_decode', 'sing'];

    public function __construct(protected array $metas)
    {
        //
    }

    public static function make(array $metas): self
    {
        return new self($metas);
    }

    /**
     * All characters with all styles.
     */
    public function all(): array
    {
        return $this->normalize()->all();
    }

    /**
     * Characters with only talk-type styles.
     */
    public function speakers(): array
    {
        return $this->filterByStyleTypes(self::TALK_STYLE_TYPES);
    }

    /**
     * Characters with only sing-type styles.
     */
    public function singers(): array
    {
        return $this->filterByStyleTypes(self::SING_STYLE_TYPES);
    }

    protected function normalize(): Collection
    {
        return collect($this->metas)->map(function ($character) {
            $character = (array) $character;
            $character['styles'] = collect($character['styles'])
                ->map(fn ($style) => (array) $style)
                ->map(function ($style) {
                    Arr::forget($style, 'order');
                    return $style;
                })
                ->all();

            return $character;
        })->map(function ($character) {
            Arr::forget($character, 'order');
            return $character;
        });
    }

    protected function filterByStyleTypes(array $types): array
    {
        return $this->normalize()
            ->map(function (array $character) use ($types) {
                $character['styles'] = collect($character['styles'])
                    ->filter(fn (array $style) => in_array($style['type'], $types, strict: true))
                    ->values()
                    ->all();

                return $character;
            })
            ->filter(fn (array $character) => count($character['styles']) > 0)
            ->values()
            ->all();
    }
}
