<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Revolution\Voicevox\Engine\Http\AudioQueryController;
use Revolution\Voicevox\Engine\Http\SingerInfoController;
use Revolution\Voicevox\Engine\Http\SingersController;
use Revolution\Voicevox\Engine\Http\SpeakerInfoController;
use Revolution\Voicevox\Engine\Http\SpeakersController;
use Revolution\Voicevox\Engine\Http\SynthesisController;
use Revolution\Voicevox\Engine\Http\VersionController;

Route::post('/audio_query', AudioQueryController::class);
Route::post('/synthesis', SynthesisController::class);
Route::get('/speakers', SpeakersController::class);
Route::get('/speaker_info', SpeakerInfoController::class);
Route::get('/singers', SingersController::class);
Route::get('/singer_info', SingerInfoController::class);
Route::get('/version', VersionController::class);
