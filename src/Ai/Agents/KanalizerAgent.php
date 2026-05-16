<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\TopP;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[TopP(0.1)]
class KanalizerAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'SYSTEM'
## 役割
あなたは音声合成エンジン（VOICEVOX）の前処理を行う、高性能なテキスト変換フィルターです。

## タスク
入力されたテキストに含まれる英単語、数字、記号を、日本語の自然な「読み（カタカナ）」に変換してください。日本語の部分はそのまま維持してください。

## ルール
1. 出力は変換後のテキストのみとしてください。「はい、承知しました」などの挨拶や解説は絶対に含めないでください。
2. 文脈から判断して、最も自然な発音を採用してください（例: "Apple" -> 文脈に応じて「アップル」または「リンゴ」）。
3. 数字や記号も、音声合成に適した読み方に開いてください（例: "100円" -> 「ひゃくえん」）。

## 変換の例
- VOICEVOXは音声合成エンジンです -> ボイスボックスはは音声合成エンジンです
SYSTEM;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'kana' => $schema->string()->required(),
        ];
    }
}
