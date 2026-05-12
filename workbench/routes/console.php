<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Revolution\Voicevox\Client\TalkAudioQuery;
use Revolution\Voicevox\Song\Note;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\Voicevox;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

// vendor/bin/testbench voicevox:version
Artisan::command('voicevox:version', function () {
    $this->comment(Voicevox::version());
})->purpose('Display Voicevox version');

// vendor/bin/testbench voicevox:speakers
Artisan::command('voicevox:speakers', function () {
    $speakers = Voicevox::speakers();
    dump($speakers);

    $uuid = data_get($speakers, '{first}.speaker_uuid');
    $speaker = Voicevox::speaker($uuid);
    dd($speaker);
})->purpose('Display Voicevox speakers');

// vendor/bin/testbench voicevox:client:talk
Artisan::command('voicevox:client:talk', function () {
    $response = Voicevox::talk('ララベルが好きなのだ')
        ->tap(function (TalkAudioQuery $talk) {
            $talk->audio_query['speedScale'] = 1.2;
        })->generate();

    $path = $response->storeAs('client', 'talk.wav');
    $this->info(Storage::path($path));
})->purpose('Generate talk with client');

// vendor/bin/testbench voicevox:client:song
Artisan::command('voicevox:client:song', function () {
    $score = Score::make([
        Note::make(length: 15),
        Note::make(length: 45, lyric: 'ド', key: 60),
        Note::make(length: 45, lyric: 'レ', key: 62),
    ]);

    $response = Voicevox::song($score, id: 6000)
        ->generate(id: 3001);

    $path = $response->storeAs('client', 'song.wav');
    $this->info(Storage::path($path));
})->purpose('Generate song with client');
