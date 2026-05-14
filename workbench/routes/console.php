<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Audio;
use Revolution\Voicevox\Client\TalkAudioQuery;
use Revolution\Voicevox\Song\Note;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\Talk\TalkAudioQuery as NativeTalkAudioQuery;
use Revolution\Voicevox\Voicevox;

use function Revolution\Voicevox\song;
use function Revolution\Voicevox\talk;

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

    // $uuid = data_get($speakers, '{first}.speaker_uuid');
    // $speaker = Voicevox::speaker($uuid);
    // dd($speaker);
})->purpose('Display Voicevox speakers');

// vendor/bin/testbench voicevox:singers
Artisan::command('voicevox:singers', function () {
    $singers = Voicevox::singers();
    dump($singers);
})->purpose('Display Voicevox singers');

// vendor/bin/testbench voicevox:client:talk
Artisan::command('voicevox:client:talk', function () {
    $response = Voicevox::talk('ララベルが好きなのだ')
        ->tap(function (TalkAudioQuery $talk) {
            $talk->audioQuery['speedScale'] = 1.2;
        })->generate();

    $path = $response->storeAs('client', 'talk.wav');
    $this->info(Storage::path($path));
})->purpose('Generate talk with client');

// vendor/bin/testbench voicevox:client:song
Artisan::command('voicevox:client:song', function () {
    $score = Score::make([
        Note::make(length: 15), // 1音目は必ず休符
        Note::make(length: Note::len(480, 120), lyric: 'ド', key: 60),
        Note::make(length: Note::len(480, 120), lyric: 'レ', key: 62),
        Note::make(length: Note::len(960, 120), lyric: 'ミ', key: 64),
        Note::make(length: 2), // 最後も短く無音を入れるとよい
    ]);

    $response = Voicevox::song($score, teacher: 6000)
        ->generate(id: 3001);

    $path = $response->storeAs('client', 'song.wav');
    $this->info(Storage::path($path));
})->purpose('Generate song with client');

// vendor/bin/testbench voicevox:client:wave
Artisan::command('voicevox:client:wave', function () {
    $talk = Storage::get('client/talk.wav');
    $song = Storage::get('client/song.wav');

    $response = Voicevox::connectWaves([
        base64_encode($talk),
        base64_encode($song),
    ]);

    $path = $response->storeAs('client', 'wave.wav');
    $this->info(Storage::path($path));
})->purpose('Connect two waves');

// vendor/bin/testbench voicevox:native:talk
Artisan::command('voicevox:native:talk', function () {
    $response = talk('ネイティブ版なのだ', id: 1)
        ->tap(function (NativeTalkAudioQuery $talk) {
            $talk->audioQuery['speedScale'] = 1.2;
        })->generate(id: 1);

    $path = $response->storeAs('native', 'talk.wav');
    $this->info(Storage::path($path));
})->purpose('Generate talk with native');

// vendor/bin/testbench voicevox:native:song
Artisan::command('voicevox:native:song', function () {
    $score = Score::make([
        Note::make(length: 15), // 1音目は必ず休符
        Note::make(length: Note::len(480, 120), lyric: 'ド', key: 60),
        Note::make(length: Note::len(480, 120), lyric: 'レ', key: 62),
        Note::make(length: Note::len(960, 120), lyric: 'ミ', key: 64),
        Note::make(length: 2), // 最後も短く無音を入れるとよい
    ]);

    $response = song($score, teacher: 6000)
        ->generate(id: 3001);

    // 同じScoreならclientもnativeも全く同じファイル
    $path = $response->storeAs('native', 'song.wav');
    $this->info(Storage::path($path));
})->purpose('Generate song with native');

// vendor/bin/testbench voicevox:ai:client
Artisan::command('voicevox:ai:client', function () {
    $response = Audio::of('ララベルAIからも使えます')
        ->voice('春日部つむぎ')
        ->generate('voicevox-client');

    $path = $response->storeAs('ai', 'client.wav');
    $this->info(Storage::path($path));
})->purpose('Generate talk with AI SDK voicevox-client');

// vendor/bin/testbench voicevox:ai:native
Artisan::command('voicevox:ai:native', function () {
    $word = 'ララベルAIからも使えます';
    $response = Audio::of($word)
        ->male()
        ->generate('voicevox');

    $path = $response->storeAs('ai', 'native.wav');
    $this->info(Storage::path($path));
})->purpose('Generate talk with AI SDK voicevox');

// vendor/bin/testbench voicevox:kanalizer
Artisan::command('voicevox:kanalizer', function () {
    // transliterator_transliterateは期待するカタカナ変換はできないので他の方法が必要。

    $word = 'Laravel AIからも使えます';
    $this->info(transliterator_transliterate('Lower(); Latin-Katakana', $word));
})->purpose('kanalizer');
