<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Enums;

enum ParseKanaErrorCode: string
{
    case UnknownText = '判別できない読み仮名があります: {text}';
    case AccentTop = '句頭にアクセントは置けません: {text}';
    case AccentTwice = '1つのアクセント句に二つ以上のアクセントは置けません: {text}';
    case AccentNotFound = 'アクセントを指定していないアクセント句があります: {text}';
    case EmptyPhrase = '{position}番目のアクセント句が空白です';
    case InterrogationMarkNotAtEnd = 'アクセント句末以外に「？」は置けません: {text}';
    case InfiniteLoop = '処理時に無限ループになってしまいました...バグ報告をお願いします。';

    /**
     * Format the error message with the given arguments.
     *
     * @param  array<string, string>  $args
     */
    public function format(array $args = []): string
    {
        $message = $this->value;

        foreach ($args as $key => $value) {
            $message = str_replace('{'.$key.'}', $value, $message);
        }

        return $message;
    }
}
