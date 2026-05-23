<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Revolution\Voicevox\Engine\Http\AccentPhrasesController;
use Revolution\Voicevox\Engine\Http\AddPresetController;
use Revolution\Voicevox\Engine\Http\AddUserDictWordController;
use Revolution\Voicevox\Engine\Http\AudioQueryController;
use Revolution\Voicevox\Engine\Http\AudioQueryFromPresetController;
use Revolution\Voicevox\Engine\Http\CancellableSynthesisController;
use Revolution\Voicevox\Engine\Http\ConnectWavesController;
use Revolution\Voicevox\Engine\Http\CoreVersionsController;
use Revolution\Voicevox\Engine\Http\DeletePresetController;
use Revolution\Voicevox\Engine\Http\DeleteUserDictWordController;
use Revolution\Voicevox\Engine\Http\DownloadableLibrariesController;
use Revolution\Voicevox\Engine\Http\EngineManifestController;
use Revolution\Voicevox\Engine\Http\FrameSynthesisController;
use Revolution\Voicevox\Engine\Http\HomeController;
use Revolution\Voicevox\Engine\Http\ImportUserDictController;
use Revolution\Voicevox\Engine\Http\InitializeSpeakerController;
use Revolution\Voicevox\Engine\Http\InstalledLibrariesController;
use Revolution\Voicevox\Engine\Http\InstallLibraryController;
use Revolution\Voicevox\Engine\Http\IsInitializedSpeakerController;
use Revolution\Voicevox\Engine\Http\MoraDataController;
use Revolution\Voicevox\Engine\Http\MoraLengthController;
use Revolution\Voicevox\Engine\Http\MoraPitchController;
use Revolution\Voicevox\Engine\Http\MorphableTargetsController;
use Revolution\Voicevox\Engine\Http\MultiSynthesisController;
use Revolution\Voicevox\Engine\Http\PresetsController;
use Revolution\Voicevox\Engine\Http\ResourcesController;
use Revolution\Voicevox\Engine\Http\SettingController;
use Revolution\Voicevox\Engine\Http\SingerInfoController;
use Revolution\Voicevox\Engine\Http\SingersController;
use Revolution\Voicevox\Engine\Http\SingFrameAudioQueryController;
use Revolution\Voicevox\Engine\Http\SingFrameF0Controller;
use Revolution\Voicevox\Engine\Http\SingFrameVolumeController;
use Revolution\Voicevox\Engine\Http\SpeakerInfoController;
use Revolution\Voicevox\Engine\Http\SpeakersController;
use Revolution\Voicevox\Engine\Http\SupportedDevicesController;
use Revolution\Voicevox\Engine\Http\SynthesisController;
use Revolution\Voicevox\Engine\Http\SynthesisMorphingController;
use Revolution\Voicevox\Engine\Http\UninstallLibraryController;
use Revolution\Voicevox\Engine\Http\UpdatePresetController;
use Revolution\Voicevox\Engine\Http\UpdateUserDictWordController;
use Revolution\Voicevox\Engine\Http\UserDictController;
use Revolution\Voicevox\Engine\Http\ValidateKanaController;
use Revolution\Voicevox\Engine\Http\VersionController;

Route::name('voicevox.engine.')->group(function () {
    /**
     * Laravelでは難しいので常に公式エンジンにフォールバック
     */
    // 最難関なのでフォールバックのみ
    Route::post('/cancellable_synthesis', CancellableSynthesisController::class)->name('cancellable_synthesis');
    Route::post('/morphable_targets', MorphableTargetsController::class)->name('morphable_targets');
    Route::post('/synthesis_morphing', SynthesisMorphingController::class)->name('synthesis_morphing');

    // 対応不要
    Route::post('/initialize_speaker', InitializeSpeakerController::class)->name('initialize_speaker');
    Route::get('/is_initialized_speaker', IsInitializedSpeakerController::class)->name('is_initialized_speaker');
    Route::get('/downloadable_libraries', DownloadableLibrariesController::class)->name('downloadable_libraries');
    Route::get('/installed_libraries', InstalledLibrariesController::class)->name('installed_libraries');
    Route::post('/install_library/{library_uuid}', InstallLibraryController::class)->name('install_library');
    Route::post('/uninstall_library/{library_uuid}', UninstallLibraryController::class)->name('uninstall_library');

    // TODO
    Route::post('/import_user_dict', ImportUserDictController::class)->name('import_user_dict');
    Route::post('/connect_waves', ConnectWavesController::class)->name('connect_waves');

    /**
     * PHP版コアで対応可能。失敗時にフォールバックも行う。
     */
    // enable_katakana_englishには非対応
    Route::post('/audio_query', AudioQueryController::class)->name('audio_query');
    Route::post('/accent_phrases', AccentPhrasesController::class)->name('accent_phrases');

    Route::post('/synthesis', SynthesisController::class)->name('synthesis');
    Route::post('/multi_synthesis', MultiSynthesisController::class)->name('multi_synthesis');
    Route::post('/mora_data', MoraDataController::class)->name('mora_data');
    Route::post('/mora_length', MoraLengthController::class)->name('mora_length');
    Route::post('/mora_pitch', MoraPitchController::class)->name('mora_pitch');
    Route::get('/speakers', SpeakersController::class)->name('speakers');
    Route::get('/speaker_info', SpeakerInfoController::class)->name('speaker_info');
    Route::get('/singers', SingersController::class)->name('singers');
    Route::get('/singer_info', SingerInfoController::class)->name('singer_info');
    Route::post('/sing_frame_audio_query', SingFrameAudioQueryController::class)->name('sing_frame_audio_query');
    Route::post('/sing_frame_f0', SingFrameF0Controller::class)->name('sing_frame_f0');
    Route::post('/sing_frame_volume', SingFrameVolumeController::class)->name('sing_frame_volume');
    Route::post('/frame_synthesis', FrameSynthesisController::class)->name('frame_synthesis');

    // 公式エンジンとデータは共有できない
    Route::get('/presets', PresetsController::class)->name('presets');
    Route::post('/add_preset', AddPresetController::class)->name('add_preset');
    Route::post('/update_preset', UpdatePresetController::class)->name('update_preset');
    Route::post('/delete_preset', DeletePresetController::class)->name('delete_preset');
    Route::post('/audio_query_from_preset', AudioQueryFromPresetController::class)->name('audio_query_from_preset');
    Route::get('/user_dict', UserDictController::class)->name('user_dict');
    Route::post('/user_dict_word', AddUserDictWordController::class)->name('add_user_dict_word');
    Route::put('/user_dict_word/{word_uuid}', UpdateUserDictWordController::class)->name('update_user_dict_word');
    Route::delete('/user_dict_word/{word_uuid}', DeleteUserDictWordController::class)->name('delete_user_dict_word');

    // TODO
    Route::get('/core_versions', CoreVersionsController::class)->name('core_versions');
    Route::get('/supported_devices', SupportedDevicesController::class)->name('supported_devices');

    /**
     * コアもフォールバックも不要で対応可能
     */
    Route::get('/version', VersionController::class)->name('version');
    Route::get('/_resources/{hash}', ResourcesController::class)->name('resources');
    Route::post('/validate_kana', ValidateKanaController::class)->name('validate_kana');
    Route::get('/engine_manifest', EngineManifestController::class)->name('engine_manifest');

    // TODO
    // Laravel版ではCORS設定は不要だけどユーザー辞書のインポート・エクスポートが設定ページにあるので独自に作成かも
    Route::match(['GET', 'POST'], '/setting', SettingController::class)->name('setting');
    // /settingと/docsへのリンクがあるウェルカムページ
    Route::get('/', HomeController::class)->name('home');
});
