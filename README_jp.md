# VOICEVOX for Laravel

[![tests](https://github.com/invokable/laravel-voicevox/actions/workflows/tests.yml/badge.svg)](https://github.com/invokable/laravel-voicevox/actions/workflows/tests.yml)
[![linter](https://github.com/invokable/laravel-voicevox/actions/workflows/lint.yml/badge.svg)](https://github.com/invokable/laravel-voicevox/actions/workflows/lint.yml)
[![Maintainability](https://qlty.sh/badges/cc6e0ee3-e221-4c06-90be-4fd87b79310e/maintainability.svg)](https://qlty.sh/gh/invokable/projects/laravel-voicevox)
[![Code Coverage](https://qlty.sh/badges/cc6e0ee3-e221-4c06-90be-4fd87b79310e/coverage.svg)](https://qlty.sh/gh/invokable/projects/laravel-voicevox)

Work In Progress.

## Overview

| Feature          | Supported | Description                                                                                                         |
|------------------|-----------|---------------------------------------------------------------------------------------------------------------------|
| VOICEVOX Client  | ✅         | 公式のVOICEVOXエンジンAPIにアクセスするクライアント。FFIなしでも動きます。                                                                        | 
| VOICEVOX Core    | ✅         | [voicevox-core-php](https://github.com/invokable/voicevox-core-php) VOICEVOX COREの動的ライブラリをFFIでラップしPHPネイティブのように使えます。 |
| Laravel style    | ✅         | Laravelスタイルでvoicevox-core-phpを扱う。                                                                                   |
| Laravel AI SDK統合 | ✅         | Laravel AI SDKのAudioから使う。クライアント版とネイティブ版、両方で対応しています。                                                                 |
| VOICEVOX Engine  | ⚠️         | PHPでは技術的に移植不可能な機能があるので公式エンジンと併用しつつ一部のみの対応です。                                                                                                                    |
| VOICEVOX Editor  | ❌         |                                                                                                                     |

## Requirements

- PHP 8.3+
- Laravel 12.x+
- FFI: クライアント以外にはFFIを有効にしたPHPが必要です。Laravel Cloudを始め一般的なWebサーバーではほとんどが無効にされているのでこのパッケージはローカルCLIでの利用を前提にしています。

## Installation

FFIが使えない環境用に`voicevox-core-php`は分けてインストールします。

```shell
composer require revolution/laravel-voicevox revolution/voicevox-core-php
```

`laravel-voicevox`だけインストールしてクライアント機能を使うこともできます。

```shell
composer require revolution/laravel-voicevox
```

### VOICEVOX CORE 動的ライブラリのセットアップ

FFIが必要な機能を使うには、[voicevox-core-phpのREADME](https://github.com/invokable/voicevox-core-php/blob/main/README_jp.md) を参考にVOICEVOX COREをインストールしてください。
