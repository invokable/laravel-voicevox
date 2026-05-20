## 将来的な計画

今の所計画はなく環境が変わったらの話。

- ~~VOICEVOXコア：RubyやGoなどの各言語版のFFIラッパーが作られているのでPHPのFFIでも同じように実装は可能なはず。実装はできてもPHPの場合は動かす環境に課題がある。PHPではFFIは無効にされていることが多い。何よりLaravel Cloudで無効なので実装しても簡単に使える環境を用意できない。homebrew/MacやWSL/WindowsのPHPならFFIが有効なので「ローカル限定」なら可能かもしれない。~~
- ~~VOICEVOXエンジン：コアの移植さえできればエンジンは簡単。別に作る必要もなくパッケージ内からルートを提供できる。~~
- VOICEVOXアプリ：ローカル限定でもいいのでエディターも実装。歌声特化。Laravelではないかも。
- 他言語版のFFIラッパーを見てもローカルに動的ライブラリをインストール、もしくはコンパイルするアプリを想定している。
- [NativePHP](https://github.com/nativephp) でデスクトップアプリを作る場合は内部で [static-php-cli](https://github.com/crazywhalecc/static-php-cli) が使われているので動的ライブラリをFFIで使う方法で実装可能。カスタムstatic-php-cliを作る必要がある。
- ~~[ext-php-rs](https://github.com/extphprs/ext-php-rs) でRustからPHP拡張を作ればstatic-php-cliに拡張を動的リンクしてビルド可能かもしれない。OSごとに異なる。この辺りは要調査。~~ FFIで十分動くので拡張は不要そう。
