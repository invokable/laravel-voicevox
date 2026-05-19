# VOICEVOXエンジンのLaravel版開発のための技術調査

コアのPHP FFIラッパーとLaravel版クライアントを作れたので次はエンジンを作ろうとしたけど思ったより難しい。
各言語版のFFIラッパーやクライアントはあってもエンジンの移植版が少ないのは技術的な障壁がある。

最初の`/audio_query`からenable_katakana_englishはコアになくエンジンの独自実装。
https://github.com/voicevox/kanalizer

`open_talk` CLIはあるけど`pyopenjtalk.run_frontend`→`pyopenjtalk.make_label`を分けて実行はできない同じ課題を持つので無理そう。

## その後

特に難しいのはこの辺りだけど非対応でもそんなに困らないので大丈夫そうという結論。

- enable_katakana_english
- morphing
- connect_waves
- cancellable_synthesis

難しいのは公式エンジンが低レベルAPIを使っているからでコアの高レベルAPIを使えばある程度対応可能だった。
