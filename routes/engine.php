<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Revolution\Voicevox\Engine\Http\AccentPhrasesController;
use Revolution\Voicevox\Engine\Http\AddPresetController;
use Revolution\Voicevox\Engine\Http\AudioQueryController;
use Revolution\Voicevox\Engine\Http\DeletePresetController;
use Revolution\Voicevox\Engine\Http\EngineManifestController;
use Revolution\Voicevox\Engine\Http\MoraDataController;
use Revolution\Voicevox\Engine\Http\MoraLengthController;
use Revolution\Voicevox\Engine\Http\MoraPitchController;
use Revolution\Voicevox\Engine\Http\PresetsController;
use Revolution\Voicevox\Engine\Http\ResourcesController;
use Revolution\Voicevox\Engine\Http\SingerInfoController;
use Revolution\Voicevox\Engine\Http\SingersController;
use Revolution\Voicevox\Engine\Http\SpeakerInfoController;
use Revolution\Voicevox\Engine\Http\SpeakersController;
use Revolution\Voicevox\Engine\Http\SynthesisController;
use Revolution\Voicevox\Engine\Http\UpdatePresetController;
use Revolution\Voicevox\Engine\Http\VersionController;

/**
 * Laravelでは難しいので常に公式エンジンにフォールバック
 */
// audio_query_from_preset次第
Route::get('/presets', PresetsController::class);
Route::post('/add_preset', AddPresetController::class);
Route::post('/update_preset', UpdatePresetController::class);
Route::post('/delete_preset', DeletePresetController::class);

/**
 * PHP版コアで対応可能。失敗時にフォールバックも行う。
 */
// enable_katakana_englishには非対応
Route::post('/audio_query', AudioQueryController::class);
// is_kana=true時のみ対応
Route::post('/accent_phrases', AccentPhrasesController::class);

Route::post('/synthesis', SynthesisController::class);
Route::post('/mora_data', MoraDataController::class);
Route::post('/mora_length', MoraLengthController::class);
Route::post('/mora_pitch', MoraPitchController::class);
Route::get('/speakers', SpeakersController::class);
Route::get('/speaker_info', SpeakerInfoController::class);
Route::get('/singers', SingersController::class);
Route::get('/singer_info', SingerInfoController::class);

/**
 * コアもフォールバックも不要で対応可能
 */
Route::get('/version', VersionController::class);
Route::get('/engine_manifest', EngineManifestController::class);
Route::get('/_resources/{hash}', ResourcesController::class)->name('voicevox.resources');
