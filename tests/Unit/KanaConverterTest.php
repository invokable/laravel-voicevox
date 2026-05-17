<?php

declare(strict_types=1);

use Revolution\Voicevox\Support\KanaConverter;

describe('validate', function () {
    it('returns true for valid kana', function () {
        expect(KanaConverter::validate("ズ'ンダモン"))->toBeTrue();
        expect(KanaConverter::validate("ラ'ラベル"))->toBeTrue();
        expect(KanaConverter::validate("コ'ンニチワ"))->toBeTrue();
    });

    it('returns true for interrogative', function () {
        expect(KanaConverter::validate("ソ'ウデスカ？"))->toBeTrue();
    });

    it('returns true for multiple phrases', function () {
        expect(KanaConverter::validate("ズ'ンダ/モ'ン"))->toBeTrue();
        expect(KanaConverter::validate("ズ'ンダ、モ'ン"))->toBeTrue();
    });

    it('returns true for unvoiced mora', function () {
        expect(KanaConverter::validate("ス'_キ"))->toBeTrue();
    });

    it('returns false for empty string', function () {
        expect(KanaConverter::validate(''))->toBeFalse();
    });

    it('returns false when accent is missing', function () {
        expect(KanaConverter::validate('アイウ'))->toBeFalse();
    });

    it('returns false when accent is at top', function () {
        expect(KanaConverter::validate("'アイウ"))->toBeFalse();
    });

    it('returns false when accent is defined twice', function () {
        expect(KanaConverter::validate("ア'イ'ウ"))->toBeFalse();
    });

    it('returns false for interrogation mark not at end', function () {
        expect(KanaConverter::validate("ア？'イ"))->toBeFalse();
    });

    it('returns false for unknown character', function () {
        expect(KanaConverter::validate("あ'いう"))->toBeFalse();
    });
});

describe('parse', function () {
    it('parses single phrase', function () {
        $result = KanaConverter::parse("ズ'ンダモン");

        expect($result)->toHaveCount(1);
        expect($result[0]['accent'])->toBe(1);
        expect($result[0]['pause'])->toBeFalse();
        expect($result[0]['interrogative'])->toBeFalse();
        expect($result[0]['moras'])->toHaveCount(5);
        expect($result[0]['moras'][0]['text'])->toBe('ズ');
        expect($result[0]['moras'][0]['consonant'])->toBe('z');
        expect($result[0]['moras'][0]['vowel'])->toBe('u');
    });

    it('parses interrogative phrase', function () {
        $result = KanaConverter::parse("ソ'ウデスカ？");

        expect($result[0]['interrogative'])->toBeTrue();
        expect($result[0]['moras'])->toHaveCount(5);
    });

    it('parses nopause delimiter', function () {
        $result = KanaConverter::parse("ズ'ンダ/モ'ン");

        expect($result)->toHaveCount(2);
        expect($result[0]['pause'])->toBeFalse();
    });

    it('parses pause delimiter', function () {
        $result = KanaConverter::parse("ズ'ンダ、モ'ン");

        expect($result)->toHaveCount(2);
        expect($result[0]['pause'])->toBeTrue();
    });

    it('parses unvoiced mora', function () {
        $result = KanaConverter::parse("ス'_キ");

        expect($result[0]['moras'][1]['text'])->toBe('キ');
        expect($result[0]['moras'][1]['vowel'])->toBe('I'); // unvoiced uppercase
    });

    it('parses compound mora with longest match', function () {
        $result = KanaConverter::parse("キャ'ラ");

        expect($result[0]['moras'][0]['text'])->toBe('キャ');
        expect($result[0]['moras'][0]['consonant'])->toBe('ky');
        expect($result[0]['moras'][0]['vowel'])->toBe('a');
    });

    it('parses N mora', function () {
        $result = KanaConverter::parse("ズ'ンダ");

        $nMora = $result[0]['moras'][1];
        expect($nMora['text'])->toBe('ン');
        expect($nMora['consonant'])->toBeNull();
        expect($nMora['vowel'])->toBe('N');
    });

    it('throws on empty string', function () {
        KanaConverter::parse('');
    })->throws(InvalidArgumentException::class);

    it('throws on missing accent', function () {
        KanaConverter::parse('アイウ');
    })->throws(InvalidArgumentException::class);
});

describe('create', function () {
    it('round-trips parse then create', function () {
        $inputs = [
            "ズ'ンダモン",
            "ラ'ラベル",
            "ズ'ンダ/モ'ン",
            "ズ'ンダ、モ'ン",
            "ソ'ウデスカ？",
            "ス'_キ",
            "キャ'ラ",
        ];

        foreach ($inputs as $input) {
            expect(KanaConverter::create(KanaConverter::parse($input)))->toBe($input);
        }
    });

    it('generates unvoiced prefix in output', function () {
        $phrases = KanaConverter::parse("_ホ'シイ");
        $output = KanaConverter::create($phrases);

        expect($output)->toBe("_ホ'シイ");
    });
});
