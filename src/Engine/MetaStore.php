<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class MetaStore
{
    protected const array TALK_STYLE_TYPES = ['talk'];

    protected const array SING_STYLE_TYPES = ['singing_teacher', 'frame_decode', 'sing'];

    protected const array DEFAULT_SUPPORTED_FEATURES = [
        'permitted_synthesis_morphing' => 'ALL',
    ];

    /** @var array<string, array<string, string>> UUID => supported_features */
    protected array $supportedFeatures = [];

    public function __construct(
        protected array $metas,
        protected ?string $characterInfoPath = null,
    ) {
        if ($characterInfoPath !== null && File::isDirectory($characterInfoPath)) {
            $this->loadSupportedFeatures($characterInfoPath);
        }
    }

    /**
     * @param  string|null  $characterInfoPath  Path to the character_info directory.
     *                                          Defaults to the package's own resources/character_info.
     */
    public static function make(array $metas, ?string $characterInfoPath = null): self
    {
        return new self($metas, $characterInfoPath ?? dirname(__DIR__, 2).'/resources/character_info');
    }

    /**
     * All characters with all styles, including supported_features.
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

    /**
     * Character info (policy, portrait, style icons/samples) for a speaker by UUID.
     *
     * @throws \RuntimeException
     */
    public function speaker(string $uuid): array
    {
        return $this->characterInfo($uuid, 'talk');
    }

    /**
     * Character info (policy, portrait, style icons/samples) for a singer by UUID.
     *
     * @throws \RuntimeException
     */
    public function singer(string $uuid): array
    {
        return $this->characterInfo($uuid, 'sing');
    }

    protected function characterInfo(string $uuid, string $type): array
    {
        if ($this->characterInfoPath === null) {
            throw new \RuntimeException('characterInfoPath is not set.');
        }

        $list = $type === 'talk' ? $this->speakers() : $this->singers();
        $character = collect($list)->firstWhere('speaker_uuid', $uuid);

        if ($character === null) {
            throw new \RuntimeException("Character not found: {$uuid}");
        }

        $charDir = $this->characterInfoPath.'/'.$uuid;

        $policy = File::get($charDir.'/policy.md');
        $portrait = base64_encode(File::get($charDir.'/portrait.png'));

        $styleInfos = [];
        foreach ($character['styles'] as $style) {
            $id = $style['id'];

            $icon = base64_encode(File::get($charDir.'/icons/'.$id.'.png'));

            $stylePortraitPath = $charDir.'/portraits/'.$id.'.png';
            $stylePortrait = File::exists($stylePortraitPath)
                ? base64_encode(File::get($stylePortraitPath))
                : null;

            $voiceSamples = [];
            for ($j = 1; $j <= 3; $j++) {
                $num = str_pad((string) $j, 3, '0', STR_PAD_LEFT);
                $voiceSamples[] = base64_encode(File::get($charDir.'/voice_samples/'.$id.'_'.$num.'.wav'));
            }

            $styleInfos[] = [
                'id' => $id,
                'icon' => $icon,
                'portrait' => $stylePortrait,
                'voice_samples' => $voiceSamples,
            ];
        }

        return [
            'policy' => $policy,
            'portrait' => $portrait,
            'style_infos' => $styleInfos,
        ];
    }

    protected function loadSupportedFeatures(string $path): void
    {
        foreach (File::directories($path) as $dir) {
            $uuid = basename($dir);
            $metasFile = $dir.'/metas.json';

            if (File::exists($metasFile)) {
                $data = json_decode(File::get($metasFile), associative: true);
                $this->supportedFeatures[$uuid] = $data['supported_features'] ?? self::DEFAULT_SUPPORTED_FEATURES;
            }
        }
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

            $uuid = $character['speaker_uuid'];
            $character['supported_features'] = $this->supportedFeatures[$uuid] ?? self::DEFAULT_SUPPORTED_FEATURES;

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
