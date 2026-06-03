<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Song;

use Illuminate\Contracts\Support\Arrayable;

readonly class Note implements Arrayable
{
    private const float FRAME_RATE = 93.75;

    private const int TPQN = 480;

    /**
     * 音符または休符。
     *
     * @param  int  $length  音符のフレーム長。秒数に93.75をかけ、端数を調整して整数にしたもの。例として125BPM (Beats Per Minute)における一拍は: 93.75[フレーム/秒] / (125[拍/分] / 60[秒/分]) = 45[フレーム/拍]
     * @param  string  $lyric  歌詞。音符の場合、一つのモーラを表すひらがな/カタカナ（例: "ド", "ファ"）。休符の場合、空文字列。
     * @param  null|int  $key  音階。音符の場合、MIDIのnote number（例: C4なら 60）。休符の場合、null
     * @param  null|string  $id  FrameAudioQueryを生成するときにFramePhoneme.note_idにコピーされる。歌唱音声には影響しない。
     */
    public function __construct(
        public int $length,
        public string $lyric = '',
        public ?int $key = null,
        public ?string $id = null,
    ) {
        //
    }

    public static function make(int $length, string $lyric = '', ?int $key = null, ?string $id = null): self
    {
        return new self($length, $lyric, $key, $id);
    }

    /**
     * フレーム長計算ヘルパー。MIDIに慣れてる人向け。整数に丸めるので徐々に誤差が生じる。
     *
     * @param  int  $ticks  4分音符一つが480として長さを入力。MIDIの分解能480。
     * @param  int  $bpm  Beats Per Minute。テンポ。
     */
    public static function len(int $ticks, int $bpm = 125): int
    {
        return (int) round((($ticks / self::TPQN) * (60 / $bpm)) * self::FRAME_RATE);
    }

    public function toArray(): array
    {
        return array_filter([
            'frame_length' => $this->length,
            'lyric' => $this->lyric,
            'key' => $this->key,
            'id' => $this->id,
        ], fn ($v) => ! is_null($v));
    }
}
