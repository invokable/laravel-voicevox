<?php

declare(strict_types=1);

use Revolution\Voicevox\Song\Note;
use Revolution\Voicevox\Song\Score;

test('Score', function () {
    $score = Score::make([
        Note::make(length: 15), // 1音目は必ず休符
    ])->add(new Note(length: 45, lyric: 'ド', key: 60));

    expect($score->toArray())->toBe([
        'notes' => [
            ['frame_length' => 15, 'lyric' => ''],
            ['frame_length' => 45, 'lyric' => 'ド', 'key' => 60],
        ],
    ]);
});

test('Note frame length', function () {
    $len = Note::len(480, 120);

    expect($len)->toBe(47);
});
