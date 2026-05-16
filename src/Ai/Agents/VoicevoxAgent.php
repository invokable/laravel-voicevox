<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class VoicevoxAgent implements Agent, HasStructuredOutput
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
入力されたテキストをAquesTalk風記法カタカナに変換してください。

## AquesTalk風記法の規則
- 読みはカタカナのみ
- `/` で区切り
- `、` で無音付き区切り
- `_` で無声化
- `'` でアクセント位置
- `？` で疑問文
- アクセント位置はちょうど１つ

## ルール
1. 出力は変換後のテキストのみとしてください。「はい、承知しました」などの挨拶や解説は絶対に含めないでください。
2. 英単語、数字、記号を、日本語の自然な「読み（カタカナ）」に変換してください。
3. 文脈から判断して、最も自然な発音を採用してください（例: "Apple" -> 文脈に応じて「アップル」または「リンゴ」）。
4. 数字や記号も、音声合成に適した読み方に開いてください（例: "100円" -> 「ひゃくえん」）。
5 カタカナと記法以外の文字を含めないでください。（「。」など）

## 変換後の例
- コンニチワ'
- ディイプラ'アニングワ/バンノ'オヤクデワ/アリマセ'ン
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
