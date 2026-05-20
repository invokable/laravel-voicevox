# VOICEVOX エンジン

公式互換のWeb APIを作る。  
コアになくてエンジン独自の機能がかなり多いので実際に作るのは簡単ではなさそう。  
[調査結果](../docs/engine-challenges-en.md) から完全な移植は不可能そう。さらによく調査すると公式エンジンは低レベルAPIを使っているから難しくて、コアの高レベルAPIを使えばエンジン独自機能を多少妥協すれば移植可能なことも判明した。

難しいエンドポイントは公式エンジンに委譲→公式エンジンが起動してない場合はエラーレスポンスを返す。  
PHP版コアでも簡単に実装可能な機能はLaravel版で直接対応。  
対応方針は決まったので実装していける。

名前空間：`Revolution\Voicevox\Engine`

- routes/engine.php: ルート
- src/Engine/Http/: Controllerクラスを配置。一応分かりやすくControllerの名前を付けるけど何も継承しない。`__invoke()`だけのシングルアクションコントローラー、APIリソース、APIシングルトンリソースなどで作成。Controllerファイルは増えてもいいのでAPIごとに分割。
- src/VoicevoxServiceProvider.php: エンジンルートを登録
- config/voicevox.php: 不要な場合もあるだろうからエンジンルートの無効化設定
- docs/engine-api.md: エンジンAPIの対応表

```php
return [
    //  他の設定

    'engine' => [
        'disabled' => env('VOICEVOX_ENGINE_DISABLED', false),
    ],
]
```

### ローカルでもウェブサーバーではデフォルト無効

php artisan serveやtestbench serveで使用するにはphp.iniでFFIを有効化する。

```
ffi.enable=true
```

### キャラクター情報・リソース

`resources/character_info/`内に公式エンジンと同じリソースが必要。
500MB以上なので別途インストール。Laravelプロジェクト直下ではなく、vendorのこのパッケージ内にダウンロードされる。

ユーザー向け通常のLaravelプロジェクト。
```bash
php artisan voicevox:install
```
開発環境のtestbench。
```bash
vendor/bin/testbench voicevox:install
```

`/speakers`から`/singer_info`、`/_resources/{hash}`のルート辺りは公式とほぼ同じ機能で再現できた。

engine_manifest.jsonはインストールなどの変換処理は挟まず公式を参考にLaravel用に作ればいいはず。
`voicevox_engine/engine_manifest.json`

VVPPファイルとして配布するなら`engine_manifest.json`はプロジェクト直下だけど配布はしないだろうから仮で`resources/engine_manifest.json`に配置。

### ユーザー辞書

公式エンジンは`resources/default.csv`にデフォルト辞書。
OSごとに違うユーザーフォルダに`user_dict.json`。
`voicevox_engine/voicevox_engine/user_dict/user_dict_manager.py`

pyopenjtalkを使っていてLaravelでは難しいパターンなのでコアの機能だけを使った版。コアのデータはバイナリではなくただのjsonなのでインポートも対応できるはず。
`src/Engine/NativeUserDict.php`

コアの機能を使ってユーザー辞書を有効にしてテキストからaccent_phrases→audio_queryの作成は成功。  
workbench/routes/console.phpの`voicevox:native:dict-talk`

### プリセット機能

公式エンジンでは`presets.yaml`ファイルを使って管理。おそらくDocker内にしかない。Dockerの公式エンジンとLaravelではプリセット設定を共有できない。

presets.jsonに保存する方法で独自実装。
`src/Engine/NativePresetStore.php`

プリセットは独自に作っても`/audio_query_from_preset`で使える。

### kanalizer

エンジンAPIにある`enable_katakana_english`は英語をカタカナに変換する機能。  
コアにはないので対応できない部分だったけどよく考えたらVOICEVOXの前にLLMを挟む運用でカバーできるかもしれない。

ここに気付いたことで、通常の日本語からAquesTalk風記法カタカナもLLMで変換すればいいのではと閃いて調べたら対応可能そうだった。AquesTalk風記からの音声合成はコアのみで可能。

Laravel AI SDK用のAquesTalk風記法カタカナ変換エージェントのサンプル。他でも同じようなプロンプトで可能なはず。  
`src/Ai/Agents/AquesTalkAgent.php`

KanalizerAgentの方が正常に動くけど漢字までひらがなにしているのでルールの調整は必要かも。
`src/Ai/Agents/KanalizerAgent.php`

AudioQuery内に`kana`が含まれてるのでコアを使う強引な方法でもAquesTalk風記法カタカナ化の実現は可能。
`src/Engine/Katakana.php`

### ライブラリダウンロード・インストール機能

これはOSごとに違うユーザー領域にダウンロードする機能。Laravel版では関係ないのでフォールバックのみ。

公式エンジンの`get_save_dir()`を使ってる機能はほとんど無視になるはず。ユーザー辞書やプリセットのように軽いjsonならstorageを使用。

### cancellable_synthesis

公式エンジンでも実験的機能でデフォルト無効なので、Laravel版では無理に対応は不要。

### 音声モデルファイル(.vvm)とスタイルIDの対応表

コアではvvmを読み込んでからスタイルIDを指定して使う。エンジンAPIでは全モデルを読み込んでるのでスタイルIDだけで全部使える。全部読み込むと遅いのでconfigで設定できるようにする。  
https://github.com/VOICEVOX/voicevox_vvm
