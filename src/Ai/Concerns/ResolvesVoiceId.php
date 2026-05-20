<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Ai\Concerns;

trait ResolvesVoiceId
{
    /**
     * Resolve a voice alias or numeric string to a VOICEVOX style ID.
     *
     * Named aliases are provided for convenience; any other value is cast to int
     * and used as a raw style ID.
     */
    protected function resolveVoiceId(string $voice): int
    {
        return match ($voice) {
            // ずんだもん
            'ずんだもん', 'ずんだもん/あまあま' => 1,
            'ずんだもん/ノーマル' => 3,
            'ずんだもん/セクシー' => 5,
            'ずんだもん/ツンツン' => 7,
            'ずんだもん/ささやき' => 22,
            'ずんだもん/ヒソヒソ' => 38,

            // 四国めたん
            '四国めたん/あまあま' => 0,
            '四国めたん', '四国めたん/ノーマル' => 2,
            '四国めたん/セクシー' => 4,
            '四国めたん/ツンツン' => 6,
            '四国めたん/ヒソヒソ' => 37,

            // その他のキャラクター
            '春日部つむぎ' => 8,
            '波音リツ' => 9,
            '雨晴はう' => 10,
            '玄野武宏' => 11,
            '白上虎太郎' => 12,
            '青山龍星' => 13,
            '冥鳴ひまり' => 14,
            '九州そら' => 16,

            // 汎用エイリアス
            'default-female' => 10,
            'default-male' => 12,

            default => (int) $voice,
        };
    }
}
