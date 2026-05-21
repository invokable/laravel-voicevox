<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Talk;

use InvalidArgumentException;
use Revolution\Voicevox\Engine\NativePresetStore;
use Revolution\Voicevox\Synthesizer;

class Talk
{
    /**
     * Preset scale fields that override the AudioQuery defaults.
     */
    private const array PRESET_SCALES = [
        'speedScale',
        'pitchScale',
        'intonationScale',
        'volumeScale',
        'prePhonemeLength',
        'postPhonemeLength',
        'pauseLength',
        'pauseLengthScale',
    ];

    public static function make(): self
    {
        return new self;
    }

    /**
     * テキスト音声合成。
     */
    public function talk(string $text, int|string $id = 1, ?int $preset = null): TalkAudioQuery
    {
        if (! empty($preset)) {
            return $this->preset($text, $preset);
        }

        $audioQuery = json_decode(Synthesizer::createAudioQuery($text, $id), true);

        return new TalkAudioQuery($audioQuery, $id);
    }

    /**
     * AquesTalk風記法カタカナから音声合成。
     */
    public function kana(string $kana, int|string $id = 1): TalkAudioQuery
    {
        $audioQuery = json_decode(Synthesizer::createAudioQueryFromKana($kana, $id), true);

        return new TalkAudioQuery($audioQuery, $id);
    }

    /**
     * プリセットから音声合成。
     */
    public function preset(string $text, int $presetId): TalkAudioQuery
    {
        $preset = app(NativePresetStore::class)->find($presetId);

        if (empty($preset)) {
            throw new InvalidArgumentException('Preset not found');
        }

        $styleId = (int) $preset['style_id'];

        $audioQuery = json_decode(Synthesizer::createAudioQuery($text, $styleId), true);

        // Apply preset-level scale overrides
        foreach (self::PRESET_SCALES as $key) {
            if (isset($preset[$key])) {
                $audioQuery[$key] = $preset[$key];
            }
        }

        return new TalkAudioQuery($audioQuery, $styleId);
    }
}
