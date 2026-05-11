<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Revolution\Voicevox\Client\TalkAudioQuery;
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

// vendor/bin/testbench voicevox:talk
Artisan::command('voicevox:talk', function () {
    $response = Voicevox::talk('ララベルが好きなのだ')
        ->tap(function (TalkAudioQuery $talk) {
            $talk->audio_query['speedScale'] = 1.2;
        })->generate();

    $path = $response->storeAs('talks', 'talk.wav');
    $this->info(Storage::path($path));
})->purpose('Generate talk with client');
