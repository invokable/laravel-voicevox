<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Revolution\Voicevox\Engine\Http\AccentPhrasesController;
use Revolution\Voicevox\Engine\Http\AliveController;
use Revolution\Voicevox\Engine\Http\CancellableSynthesisController;
use Revolution\Voicevox\Engine\Http\ConnectWavesController;
use Revolution\Voicevox\Engine\Http\CoreVersionsController;
use Revolution\Voicevox\Engine\Http\DownloadableLibrariesController;
use Revolution\Voicevox\Engine\Http\FrameSynthesisController;
use Revolution\Voicevox\Engine\Http\InitializeSpeakerController;
use Revolution\Voicevox\Engine\Http\InstalledLibrariesController;
use Revolution\Voicevox\Engine\Http\InstallLibraryController;
use Revolution\Voicevox\Engine\Http\IsInitializedSpeakerController;
use Revolution\Voicevox\Engine\Http\MorphableTargetsController;
use Revolution\Voicevox\Engine\Http\MultiSynthesisController;
use Revolution\Voicevox\Engine\Http\SettingController;
use Revolution\Voicevox\Engine\Http\SingFrameAudioQueryController;
use Revolution\Voicevox\Engine\Http\SingFrameF0Controller;
use Revolution\Voicevox\Engine\Http\SingFrameVolumeController;
use Revolution\Voicevox\Engine\Http\SupportedDevicesController;
use Revolution\Voicevox\Engine\Http\SynthesisMorphingController;
use Revolution\Voicevox\Engine\Http\UninstallLibraryController;
use Revolution\Voicevox\Engine\Http\AddPresetController;
use Revolution\Voicevox\Engine\Http\AddUserDictWordController;
use Revolution\Voicevox\Engine\Http\AudioQueryController;
use Revolution\Voicevox\Engine\Http\AudioQueryFromPresetController;
use Revolution\Voicevox\Engine\Http\DeletePresetController;
use Revolution\Voicevox\Engine\Http\DeleteUserDictWordController;
use Revolution\Voicevox\Engine\Http\EngineManifestController;
use Revolution\Voicevox\Engine\Http\ImportUserDictController;
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
use Revolution\Voicevox\Engine\Http\UpdateUserDictWordController;
use Revolution\Voicevox\Engine\Http\UserDictController;
use Revolution\Voicevox\Engine\Http\ValidateKanaController;
use Revolution\Voicevox\Engine\Http\VersionController;

/**
 * Laravelでは難しいので常に公式エンジンにフォールバック
 */
// プリセットを共有できないので対応方法は未定
Route::get('/presets', PresetsController::class);
Route::post('/add_preset', AddPresetController::class);
Route::post('/update_preset', UpdatePresetController::class);
Route::post('/delete_preset', DeletePresetController::class);
Route::post('/audio_query_from_preset', AudioQueryFromPresetController::class);

// TODO
Route::get('/user_dict', UserDictController::class);
Route::post('/user_dict_word', AddUserDictWordController::class);
Route::put('/user_dict_word/{word_uuid}', UpdateUserDictWordController::class);
Route::delete('/user_dict_word/{word_uuid}', DeleteUserDictWordController::class);
Route::post('/import_user_dict', ImportUserDictController::class);

/**
 * PHP版コアで対応可能。失敗時にフォールバックも行う。
 */
// enable_katakana_englishには非対応
Route::post('/audio_query', AudioQueryController::class);
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
Route::get('/core_versions', CoreVersionsController::class);
Route::get('/supported_devices', SupportedDevicesController::class);

Route::post('/cancellable_synthesis', CancellableSynthesisController::class);
Route::post('/multi_synthesis', MultiSynthesisController::class);
Route::post('/connect_waves', ConnectWavesController::class);

Route::post('/morphable_targets', MorphableTargetsController::class);
Route::post('/synthesis_morphing', SynthesisMorphingController::class);

Route::post('/sing_frame_audio_query', SingFrameAudioQueryController::class);
Route::post('/sing_frame_f0', SingFrameF0Controller::class);
Route::post('/sing_frame_volume', SingFrameVolumeController::class);
Route::post('/frame_synthesis', FrameSynthesisController::class);

Route::post('/initialize_speaker', InitializeSpeakerController::class);
Route::get('/is_initialized_speaker', IsInitializedSpeakerController::class);

Route::get('/downloadable_libraries', DownloadableLibrariesController::class);
Route::get('/installed_libraries', InstalledLibrariesController::class);
Route::post('/install_library/{library_uuid}', InstallLibraryController::class);
Route::post('/uninstall_library/{library_uuid}', UninstallLibraryController::class);

Route::match(['GET', 'POST'], '/setting', SettingController::class);

Route::get('/', AliveController::class)->name('voicevox.alive');

Route::get('/_resources/{hash}', ResourcesController::class)->name('voicevox.resources');
Route::post('/validate_kana', ValidateKanaController::class);
Route::get('/engine_manifest', EngineManifestController::class);
