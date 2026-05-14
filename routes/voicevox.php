<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Revolution\Voicevox\Engine\Http\AudioQueryController;

Route::post('/audio_query', AudioQueryController::class);
