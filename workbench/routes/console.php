<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Audio;
use Revolution\Voicevox\Ai\Agents\AquesTalkAgent;
use Revolution\Voicevox\Ai\Agents\KanalizerAgent;
use Revolution\Voicevox\Client\TalkAudioQuery;
use Revolution\Voicevox\Core\OpenJtalk;
use Revolution\Voicevox\Core\UserDict;
use Revolution\Voicevox\Core\VoiceModelFile;
use Revolution\Voicevox\Core\VoicevoxCore;
use Revolution\Voicevox\Engine\Katakana;
use Revolution\Voicevox\Song\Note;
use Revolution\Voicevox\Song\Score;
use Revolution\Voicevox\Support\KanaConverter;
use Revolution\Voicevox\Synthesizer;
use Revolution\Voicevox\Talk\TalkAudioQuery as NativeTalkAudioQuery;
use Revolution\Voicevox\Voicevox;

use function Revolution\Voicevox\dict;
use function Revolution\Voicevox\kana;
use function Revolution\Voicevox\preset;
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
    $response = Voicevox::talk('Laravelが好きなのだ')
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

// vendor/bin/testbench voicevox:native:meta
// config/voicevox.php で指定しているコアで読み込んでいるモデルの情報
Artisan::command('voicevox:native:meta', function () {
    $meta = Synthesizer::metas();

    $this->info($meta);
})->purpose('Show model meta');

// vendor/bin/testbench voicevox:native:model
Artisan::command('voicevox:native:model', function () {
    $voicevoxCoreDir = rtrim(config('voicevox.core.path', ''), '/').'/';
    $modelDir = $voicevoxCoreDir.trim(config('voicevox.core.models'));
    $model = VoiceModelFile::open($modelDir.'/0.vvm');

    $this->info($model->createMetasJson());
})->purpose('Show model meta');

// vendor/bin/testbench voicevox:native:talk
Artisan::command('voicevox:native:talk', function () {
    $response = talk('ネイティブ版なのだ', id: 1)
        ->tap(function (NativeTalkAudioQuery $talk) {
            dump($talk->audioQuery);
            $talk->audioQuery['speedScale'] = 1.2;
        })->generate(id: 1);

    $path = $response->storeAs('native', 'talk.wav');
    $this->info(Storage::path($path));
})->purpose('Generate talk with native');

// vendor/bin/testbench voicevox:native:kana
Artisan::command('voicevox:native:kana', function () {
    $response = kana("ネイティブ'バンナ/ノダ'", id: 1)
        ->generate(id: 1);

    $path = $response->storeAs('native', 'kana.wav');
    $this->info(Storage::path($path));
})->purpose('Generate talk with native kana');

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

// vendor/bin/testbench voicevox:ai:kana-agent
Artisan::command('voicevox:ai:kana-agent', function () {
    $word = 'Laravel AI SDKを使ってAquesTalk風記法カタカナに変換しました。';
    $response = AquesTalkAgent::make()
        ->prompt(
            $word,
        );

    $this->info($response->text);

    if (! KanaConverter::validate($response->text)) {
        $this->error('正常に変換できていません：'.$response->text);

        return;
    }

    // 上手く変換できるとは限らないので直接音声化は難しい。
    // カタカナ→人間が確認・修正→音声化

    // $word = "ララベ'ル/エイア'イ/エスディイケ'イオ/ツカ'ッテ/アケストオクフウキホウカタカナニ'/ヘンカンシマシタ'";
    $response = kana($response->text, id: 1)
        ->tap(function (NativeTalkAudioQuery $talk) {
            // audioQueryのkanaにコアによる変換結果が含まれているので下のkanalizer変換だけ行って通常のtalkを使った方がAquesTalk風記法カタカナへの変換は確実かもしれない。
            dump($talk->audioQuery['kana'] ?? '');
        })
        ->generate(id: 1);

    $path = $response->storeAs('native', 'kana-agent.wav');
    $this->info(Storage::path($path));
})->purpose('AquesTalkAgent');

// vendor/bin/testbench voicevox:ai:kanalizer
Artisan::command('voicevox:ai:kanalizer', function () {
    $word = 'Laravel AI SDKを使ってkanalizer風のカタカナ変換を行います';
    $response = KanalizerAgent::make()
        ->prompt(
            $word,
        );

    $this->info($response->text);

    // 上手く変換できるとは限らないので直接音声化は難しい。
    // カタカナ化だけなのでAquesTalkAgentよりは正常。

    $response = talk($response->text, id: 1)
        ->generate(id: 1);

    $path = $response->storeAs('native', 'kanalizer.wav');
    $this->info(Storage::path($path));
})->purpose('kanalizer');

// vendor/bin/testbench voicevox:native:aquestalk
Artisan::command('voicevox:native:aquestalk', function () {
    $word = 'コア機能を使ってカタカナに変換';
    $kana = (new Katakana)->create($word);

    if (! KanaConverter::validate($kana)) {
        $this->error('正常に変換できていません：'.$kana);

        return;
    }

    $this->info($kana);
})->purpose('AquesTalk');

// vendor/bin/testbench voicevox:validate_kana
Artisan::command('voicevox:validate_kana', function () {
    $word = 'イロハ';

    KanaConverter::parse($word);
})->purpose('validate_kana');

// vendor/bin/testbench voicevox:native:dict
Artisan::command('voicevox:native:dict', function () {
    $dict = dict()->add(
        surface: 'Laravel',
        pronunciation: 'ララベル',
        accentType: 1,
    );

    $this->info($dict);

    $all = dict()->all();
    dump($all);
})->purpose('dict');

// vendor/bin/testbench voicevox:native:dict-talk
Artisan::command('voicevox:native:dict-talk', function () {
    // コアの機能を使ってユーザー辞書を有効にしてテキストからaccent_phrases→audio_queryの作成。
    // 実行自体は成功したのでこれをLaravelで使いやすいように組み込む必要がある。

    $dict = new UserDict;
    $path = storage_path('voicevox/user_dict.json');

    if (file_exists($path)) {
        $dict->load($path);
    }

    $coreDir = rtrim(config('voicevox.core.path', ''), '/').'/';
    $dictDir = $coreDir.trim(config('voicevox.core.dict', 'dict/open_jtalk_dic_utf_8-1.11'), '/').'/';

    $openjtalk = new OpenJtalk($dictDir);
    $openjtalk->useUserDict($dict);

    $accent_phrases = $openjtalk->analyze('Laravel');
    $this->info($accent_phrases);

    $audio_query = VoicevoxCore::audioQueryCreateFromAccentPhrases($accent_phrases);
    $this->info($audio_query);
})->purpose('analyze text using user dict');

// vendor/bin/testbench voicevox:native:preset
Artisan::command('voicevox:native:preset', function () {
    $preset = [
        'id' => 1,
        'name' => 'fast',
        'style_id' => 3,
        'speedScale' => 1.5,
        'pitchScale' => 0.0,
        'intonationScale' => 1.0,
        'volumeScale' => 1.0,
        'prePhonemeLength' => 0.1,
        'postPhonemeLength' => 0.1,
    ];
    $id = preset()->add($preset);

    // idが重複している場合は自動的に増える
    $this->info($id);

    $all = preset()->all();
    dump($all);
})->purpose('preset');
