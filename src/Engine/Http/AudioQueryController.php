<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Engine\Http;

use Illuminate\Http\Request;
use Revolution\Voicevox\Synthesizer;

class AudioQueryController
{
    public function __invoke(Request $request): string
    {
        $text = $request->string('text')->value();
        $id = $request->integer('speaker');
        $enable_katakana_english = $request->boolean('enable_katakana_english', true);
        $core_version = $request->input('core_version');

        // コアにはenable_katakana_englishはないのでコアを使えば簡単にエンジンAPIが作れるわけではない。
        // エンジンのコードを調査して実装が必要。
        // return Synthesizer::createAudioQuery($text, $id);
    }
}
