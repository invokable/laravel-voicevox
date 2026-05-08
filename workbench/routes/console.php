<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Revolution\Voicevox\Client\VoiceAudioQuery;
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

// vendor/bin/testbench voicevox:voice
Artisan::command('voicevox:voice', function () {
    $response = Voicevox::voice('ララベルが好きなのだ')
        ->tap(function (VoiceAudioQuery $voice) {
            $voice->audio_query['speedScale'] = 1.2;
        })->generate();

    $path = $response->storeAs('voices', 'voice.wav');
    $this->info(Storage::path($path));
})->purpose('Generate voice');
