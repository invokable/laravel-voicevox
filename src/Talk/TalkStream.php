<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Talk;

use Closure;
use Generator;
use Illuminate\Contracts\Support\Responsable;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * ストリーミング音声合成。
 *
 * 公式エンジンの`/streaming_synthesis`と同じく、先頭に総サイズ確定済みのWAVヘッダーを置き、
 * セグメントごとにレンダリングしたPCMを順に出力する。
 */
class TalkStream implements Responsable
{
    public const float FRAME_RATE = 93.75;

    protected const int SAMPLES_PER_FRAME = 256;

    protected const int DEFAULT_SAMPLING_RATE = 24000;

    protected const int WAV_HEADER_SIZE = 44;

    /** 開始位置のフレーム数 */
    public readonly int $startFrame;

    /** 一度にレンダリングするフレーム数 */
    public readonly int $segmentFrames;

    /**
     * @param  int  $totalFrames  音声全体のフレーム数
     * @param  Closure(int, int): string  $renderer  `[$start, $end)`のフレーム範囲を16-bit PCMにして返す
     * @param  float  $startOffset  音声の開始位置（秒）
     * @param  float  $segmentLength  一度に合成する音声の長さ（秒）
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        protected int $totalFrames,
        protected int $samplingRate,
        protected bool $stereo,
        protected Closure $renderer,
        float $startOffset = 0.0,
        float $segmentLength = 0.3,
    ) {
        if ($startOffset < 0) {
            throw new InvalidArgumentException('start_offset must be greater than or equal to 0.');
        }

        if ($segmentLength <= 0) {
            throw new InvalidArgumentException('segment_length must be greater than 0.');
        }

        $this->startFrame = self::toFrame($startOffset);

        if ($this->startFrame >= $this->totalFrames) {
            throw new InvalidArgumentException('start_offset must be less than the length of the audio.');
        }

        $this->segmentFrames = max(1, self::toFrame($segmentLength));
    }

    /**
     * 秒をフレーム数に変換する。公式エンジンと同じ偶数丸め。
     */
    public static function toFrame(float $seconds): int
    {
        return (int) round($seconds * self::FRAME_RATE, 0, PHP_ROUND_HALF_EVEN);
    }

    /**
     * WAVヘッダーを含む全体のバイト数。
     */
    public function contentLength(): int
    {
        return self::WAV_HEADER_SIZE + $this->pcmLength();
    }

    public function header(): string
    {
        $channels = $this->channels();
        $blockSize = 2 * $channels;
        $pcmLength = $this->pcmLength();

        return pack(
            'a4Va4a4VvvVVvva4V',
            'RIFF',
            $pcmLength + self::WAV_HEADER_SIZE - 8,
            'WAVE',
            'fmt ',
            16,
            1,
            $channels,
            $this->samplingRate,
            $this->samplingRate * $blockSize,
            $blockSize,
            16,
            'data',
            $pcmLength,
        );
    }

    /**
     * WAVヘッダーに続いてPCMをセグメントごとに生成する。
     *
     * @return Generator<int, string>
     */
    public function chunks(): Generator
    {
        yield $this->header();

        for ($start = $this->startFrame; $start < $this->totalFrames; $start += $this->segmentFrames) {
            yield ($this->renderer)($start, min($start + $this->segmentFrames, $this->totalFrames));
        }
    }

    /**
     * ストリームせずにWAV全体をまとめて取得する。
     */
    public function content(): string
    {
        $content = '';

        foreach ($this->chunks() as $chunk) {
            $content .= $chunk;
        }

        return $content;
    }

    public function toResponse($request): Response
    {
        return response()->stream(function (): void {
            foreach ($this->chunks() as $chunk) {
                echo $chunk;
                flush();
            }
        }, 200, [
            'Content-Type' => 'audio/wav',
            'Content-Length' => (string) $this->contentLength(),
        ]);
    }

    protected function channels(): int
    {
        return $this->stereo ? 2 : 1;
    }

    protected function pcmLength(): int
    {
        $repeatCount = intdiv($this->samplingRate, self::DEFAULT_SAMPLING_RATE) * $this->channels();

        return ($this->totalFrames - $this->startFrame) * self::SAMPLES_PER_FRAME * $repeatCount * 2;
    }
}
