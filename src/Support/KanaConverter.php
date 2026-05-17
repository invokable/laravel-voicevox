<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Support;

use Revolution\Voicevox\Enums\ParseKanaErrorCode;
use Revolution\Voicevox\Exceptions\ParseKanaError;

/**
 * AquesTalk風記法カタカナのパース・生成。
 *
 * 記法の規則:
 * - 読みはカタカナのみ
 * - `/` で区切り（ポーズなし）
 * - `、` で無音付き区切り（ポーズあり）
 * - `_` で無声化（例: `_ホ`）
 * - `'` でアクセント位置（ちょうど１つ）
 * - `？` で疑問文（末尾のみ）
 *
 * @see https://github.com/VOICEVOX/voicevox_engine/blob/master/README.md#aquestalk-風記法
 */
class KanaConverter
{
    private const string UNVOICE_SYMBOL = '_';

    private const string ACCENT_SYMBOL = "'";

    private const string NOPAUSE_DELIMITER = '/';

    private const string PAUSE_DELIMITER = '、';

    private const string INTERROGATION_MARK = '？';

    /**
     * カタカナモーラ -> [子音, 母音] の対応表。
     * voicevox_engine の mora_kana_to_mora_phonemes に相当。
     *
     * @var array<string, array{0: string|null, 1: string}>
     */
    private static array $moraMap = [
        'ヴォ' => ['v', 'o'],
        'ヴェ' => ['v', 'e'],
        'ヴィ' => ['v', 'i'],
        'ヴァ' => ['v', 'a'],
        'ヴ' => ['v', 'u'],
        'ン' => [null, 'N'],
        'ワ' => ['w', 'a'],
        'ロ' => ['r', 'o'],
        'レ' => ['r', 'e'],
        'ル' => ['r', 'u'],
        'リョ' => ['ry', 'o'],
        'リュ' => ['ry', 'u'],
        'リャ' => ['ry', 'a'],
        'リェ' => ['ry', 'e'],
        'リィ' => ['ry', 'i'],
        'リ' => ['r', 'i'],
        'ラ' => ['r', 'a'],
        'ヨ' => ['y', 'o'],
        'ユ' => ['y', 'u'],
        'ヤ' => ['y', 'a'],
        'モ' => ['m', 'o'],
        'メ' => ['m', 'e'],
        'ム' => ['m', 'u'],
        'ミョ' => ['my', 'o'],
        'ミュ' => ['my', 'u'],
        'ミャ' => ['my', 'a'],
        'ミェ' => ['my', 'e'],
        'ミィ' => ['my', 'i'],
        'ミ' => ['m', 'i'],
        'マ' => ['m', 'a'],
        'ポ' => ['p', 'o'],
        'ボ' => ['b', 'o'],
        'ホ' => ['h', 'o'],
        'ペ' => ['p', 'e'],
        'ベ' => ['b', 'e'],
        'ヘ' => ['h', 'e'],
        'プ' => ['p', 'u'],
        'ブ' => ['b', 'u'],
        'フォ' => ['f', 'o'],
        'フェ' => ['f', 'e'],
        'フィ' => ['f', 'i'],
        'ファ' => ['f', 'a'],
        'フ' => ['f', 'u'],
        'ピョ' => ['py', 'o'],
        'ピュ' => ['py', 'u'],
        'ピャ' => ['py', 'a'],
        'ピェ' => ['py', 'e'],
        'ピィ' => ['py', 'i'],
        'ピ' => ['p', 'i'],
        'ビョ' => ['by', 'o'],
        'ビュ' => ['by', 'u'],
        'ビャ' => ['by', 'a'],
        'ビェ' => ['by', 'e'],
        'ビィ' => ['by', 'i'],
        'ビ' => ['b', 'i'],
        'ヒョ' => ['hy', 'o'],
        'ヒュ' => ['hy', 'u'],
        'ヒャ' => ['hy', 'a'],
        'ヒェ' => ['hy', 'e'],
        'ヒィ' => ['hy', 'i'],
        'ヒ' => ['h', 'i'],
        'パ' => ['p', 'a'],
        'バ' => ['b', 'a'],
        'ハ' => ['h', 'a'],
        'ノ' => ['n', 'o'],
        'ネ' => ['n', 'e'],
        'ヌ' => ['n', 'u'],
        'ニョ' => ['ny', 'o'],
        'ニュ' => ['ny', 'u'],
        'ニャ' => ['ny', 'a'],
        'ニェ' => ['ny', 'e'],
        'ニィ' => ['ny', 'i'],
        'ニ' => ['n', 'i'],
        'ナ' => ['n', 'a'],
        'ドゥ' => ['d', 'u'],
        'ド' => ['d', 'o'],
        'トゥ' => ['t', 'u'],
        'ト' => ['t', 'o'],
        'デョ' => ['dy', 'o'],
        'デュ' => ['dy', 'u'],
        'デャ' => ['dy', 'a'],
        'デェ' => ['dy', 'e'],
        'ディ' => ['d', 'i'],
        'デ' => ['d', 'e'],
        'テョ' => ['ty', 'o'],
        'テュ' => ['ty', 'u'],
        'テャ' => ['ty', 'a'],
        'テェ' => ['ty', 'e'],
        'ティ' => ['t', 'i'],
        'テ' => ['t', 'e'],
        'ツォ' => ['ts', 'o'],
        'ツェ' => ['ts', 'e'],
        'ツィ' => ['ts', 'i'],
        'ツァ' => ['ts', 'a'],
        'ツ' => ['ts', 'u'],
        'ッ' => [null, 'cl'],
        'チョ' => ['ch', 'o'],
        'チュ' => ['ch', 'u'],
        'チャ' => ['ch', 'a'],
        'チェ' => ['ch', 'e'],
        'チ' => ['ch', 'i'],
        'ダ' => ['d', 'a'],
        'タ' => ['t', 'a'],
        'ゾ' => ['z', 'o'],
        'ソ' => ['s', 'o'],
        'ゼ' => ['z', 'e'],
        'セ' => ['s', 'e'],
        'ズィ' => ['z', 'i'],
        'ズ' => ['z', 'u'],
        'スィ' => ['s', 'i'],
        'ス' => ['s', 'u'],
        'ジョ' => ['j', 'o'],
        'ジュ' => ['j', 'u'],
        'ジャ' => ['j', 'a'],
        'ジェ' => ['j', 'e'],
        'ジ' => ['j', 'i'],
        'ショ' => ['sh', 'o'],
        'シュ' => ['sh', 'u'],
        'シャ' => ['sh', 'a'],
        'シェ' => ['sh', 'e'],
        'シ' => ['sh', 'i'],
        'ザ' => ['z', 'a'],
        'サ' => ['s', 'a'],
        'ゴ' => ['g', 'o'],
        'コ' => ['k', 'o'],
        'ゲ' => ['g', 'e'],
        'ケ' => ['k', 'e'],
        'グヮ' => ['gw', 'a'],
        'グォ' => ['gw', 'o'],
        'グェ' => ['gw', 'e'],
        'グゥ' => ['gw', 'u'],
        'グィ' => ['gw', 'i'],
        'グ' => ['g', 'u'],
        'クヮ' => ['kw', 'a'],
        'クォ' => ['kw', 'o'],
        'クェ' => ['kw', 'e'],
        'クゥ' => ['kw', 'u'],
        'クィ' => ['kw', 'i'],
        'ク' => ['k', 'u'],
        'ギョ' => ['gy', 'o'],
        'ギュ' => ['gy', 'u'],
        'ギャ' => ['gy', 'a'],
        'ギェ' => ['gy', 'e'],
        'ギィ' => ['gy', 'i'],
        'ギ' => ['g', 'i'],
        'キョ' => ['ky', 'o'],
        'キュ' => ['ky', 'u'],
        'キャ' => ['ky', 'a'],
        'キェ' => ['ky', 'e'],
        'キィ' => ['ky', 'i'],
        'キ' => ['k', 'i'],
        'ガ' => ['g', 'a'],
        'カ' => ['k', 'a'],
        'オ' => [null, 'o'],
        'エ' => [null, 'e'],
        'ウォ' => ['w', 'o'],
        'ウェ' => ['w', 'e'],
        'ウゥ' => ['w', 'u'],
        'ウィ' => ['w', 'i'],
        'ウ' => [null, 'u'],
        'イェ' => ['y', 'e'],
        'イ' => [null, 'i'],
        'ア' => [null, 'a'],
        // additional
        'ヴョ' => ['by', 'o'],
        'ヴュ' => ['by', 'u'],
        'ヴャ' => ['by', 'a'],
        'ヲ' => [null, 'o'],
        'ヱ' => [null, 'e'],
        'ヰ' => [null, 'i'],
        'ヮ' => ['w', 'a'],
        'ョ' => ['y', 'o'],
        'ュ' => ['y', 'u'],
        'ヅ' => ['z', 'u'],
        'ヂョ' => ['j', 'o'],
        'ヂュ' => ['j', 'u'],
        'ヂャ' => ['j', 'a'],
        'ヂェ' => ['j', 'e'],
        'ヂ' => ['j', 'i'],
        'グァ' => ['gw', 'a'],
        'クァ' => ['kw', 'a'],
        'ヶ' => ['k', 'e'],
        'ャ' => ['y', 'a'],
        'ォ' => [null, 'o'],
        'ェ' => [null, 'e'],
        'ゥ' => [null, 'u'],
        'ィ' => [null, 'i'],
        'ァ' => [null, 'a'],
    ];

    /** @var array<string, array{text: string, consonant: string|null, vowel: string}>|null */
    private static ?array $kanaToMora = null;

    /**
     * カナ->モーラのルックアップテーブルを構築（無声化バリアントを含む）。
     *
     * @return array<string, array{text: string, consonant: string|null, vowel: string}>
     */
    private static function kanaToMora(): array
    {
        if (self::$kanaToMora !== null) {
            return self::$kanaToMora;
        }

        self::$kanaToMora = [];
        $voicedVowels = ['a', 'i', 'u', 'e', 'o'];

        foreach (self::$moraMap as $kana => [$consonant, $vowel]) {
            self::$kanaToMora[$kana] = [
                'text' => $kana,
                'consonant' => $consonant,
                'vowel' => $vowel,
            ];

            // 「`_` で無声化」のバリアント（例: `_ホ` -> 母音を大文字化）
            if (in_array($vowel, $voicedVowels, true)) {
                self::$kanaToMora[self::UNVOICE_SYMBOL.$kana] = [
                    'text' => $kana,
                    'consonant' => $consonant,
                    'vowel' => strtoupper($vowel),
                ];
            }
        }

        return self::$kanaToMora;
    }

    /**
     * AquesTalk風記法カタカナのバリデーション。
     */
    public static function validate(string $text): bool
    {
        try {
            self::parse($text);

            return true;
        } catch (ParseKanaError) {
            return false;
        }
    }

    /**
     * AquesTalk風記法カタカナをアクセント句配列にパース。
     *
     * 各アクセント句:
     * - moras: モーラの配列（text, consonant, vowel）
     * - accent: アクセント位置（1始まり）
     * - pause: 次のアクセント句との間にポーズを入れるか
     * - interrogative: 疑問形か
     *
     * @return array<int, array{moras: list<array{text: string, consonant: string|null, vowel: string}>, accent: int, pause: bool, interrogative: bool}>
     *
     * @throws ParseKanaError
     */
    public static function parse(string $text): array
    {
        if ($text === '') {
            throw new ParseKanaError(ParseKanaErrorCode::EmptyPhrase, ['position' => '1']);
        }

        $results = [];
        $chars = mb_str_split($text);
        $len = count($chars);
        $phraseStart = 0;

        for ($i = 0; $i <= $len; $i++) {
            if ($i === $len || $chars[$i] === self::PAUSE_DELIMITER || $chars[$i] === self::NOPAUSE_DELIMITER) {
                $phrase = implode('', array_slice($chars, $phraseStart, $i - $phraseStart));

                if ($phrase === '') {
                    throw new ParseKanaError(ParseKanaErrorCode::EmptyPhrase, ['position' => (string) (count($results) + 1)]);
                }

                $phraseStart = $i + 1;

                // 「`？` で疑問文」の実装
                $isInterrogative = str_contains($phrase, self::INTERROGATION_MARK);
                if ($isInterrogative) {
                    if (mb_strpos($phrase, self::INTERROGATION_MARK) !== mb_strlen($phrase) - 1) {
                        throw new ParseKanaError(ParseKanaErrorCode::InterrogationMarkNotAtEnd, ['text' => $phrase]);
                    }
                    $phrase = str_replace(self::INTERROGATION_MARK, '', $phrase);
                }

                $accentPhrase = self::parsePhrase($phrase);
                $accentPhrase['pause'] = $i < $len && $chars[$i] === self::PAUSE_DELIMITER;
                $accentPhrase['interrogative'] = $isInterrogative;

                $results[] = $accentPhrase;
            }
        }

        return $results;
    }

    /**
     * 単一アクセント句（区切り文字・疑問符なし）をパース。
     *
     * @return array{moras: list<array{text: string, consonant: string|null, vowel: string}>, accent: int, pause: bool, interrogative: bool}
     *
     * @throws ParseKanaError
     */
    private static function parsePhrase(string $phrase): array
    {
        $kanaToMora = self::kanaToMora();
        $chars = mb_str_split($phrase);
        $len = count($chars);

        $accentIndex = null;
        $moras = [];
        $baseIndex = 0;

        while ($baseIndex < $len) {
            // 「`'` でアクセント位置」の実装
            if ($chars[$baseIndex] === self::ACCENT_SYMBOL) {
                if (count($moras) === 0) {
                    throw new ParseKanaError(ParseKanaErrorCode::AccentTop, ['text' => $phrase]);
                }
                if ($accentIndex !== null) {
                    throw new ParseKanaError(ParseKanaErrorCode::AccentTwice, ['text' => $phrase]);
                }
                $accentIndex = count($moras);
                $baseIndex++;

                continue;
            }

            // longest matchによりモーラ化
            $stack = '';
            $matchedText = null;
            $matchedLen = 0;

            for ($watchIndex = $baseIndex; $watchIndex < $len; $watchIndex++) {
                if ($chars[$watchIndex] === self::ACCENT_SYMBOL) {
                    break;
                }
                $stack .= $chars[$watchIndex];
                if (isset($kanaToMora[$stack])) {
                    $matchedText = $stack;
                    $matchedLen = $watchIndex - $baseIndex + 1;
                }
            }

            if ($matchedText === null) {
                throw new ParseKanaError(ParseKanaErrorCode::UnknownText, ['text' => $stack]);
            }

            $moras[] = $kanaToMora[$matchedText];
            $baseIndex += $matchedLen;
        }

        if ($accentIndex === null) {
            throw new ParseKanaError(ParseKanaErrorCode::AccentNotFound, ['text' => $phrase]);
        }

        return [
            'moras' => $moras,
            'accent' => $accentIndex,
            'pause' => false,
            'interrogative' => false,
        ];
    }

    /**
     * アクセント句配列からAquesTalk風記法カタカナを生成。
     *
     * @param  array<int, array{moras: list<array{text: string, consonant: string|null, vowel: string}>, accent: int, pause: bool, interrogative: bool}>  $accentPhrases
     */
    public static function create(array $accentPhrases): string
    {
        $text = '';
        $count = count($accentPhrases);

        foreach ($accentPhrases as $i => $phrase) {
            foreach ($phrase['moras'] as $j => $mora) {
                // 「`_` で無声化」の実装
                if (in_array($mora['vowel'], ['A', 'I', 'U', 'E', 'O'], true)) {
                    $text .= self::UNVOICE_SYMBOL;
                }
                $text .= $mora['text'];

                // 「`'` でアクセント位置」の実装
                if ($j + 1 === $phrase['accent']) {
                    $text .= self::ACCENT_SYMBOL;
                }
            }

            // 「`？` で疑問文」の実装
            if ($phrase['interrogative']) {
                $text .= self::INTERROGATION_MARK;
            }

            if ($i < $count - 1) {
                // 「`/` で区切り」「`、` で無音付き区切り」の実装
                $text .= $phrase['pause'] ? self::PAUSE_DELIMITER : self::NOPAUSE_DELIMITER;
            }
        }

        return $text;
    }
}
