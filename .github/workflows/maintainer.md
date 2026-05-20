---
name: VOICEVOX メンテナー
description: VOICEVOXパッケージのメンテナンスと機能追加を自律的に行い、作業記録をDiscussion #1に記録する。
on:
    schedule: 
      - cron: daily around 17:00 utc+9
      - cron: daily around 4:00 utc+9
    workflow_dispatch:

steps:
    -   name: PHP環境のセットアップ
        uses: shivammathur/setup-php@2.37.1
        with:
            php-version: 8.5
            extensions: mbstring, dom, phar
            coverage: xdebug
    -   name: Composerの依存関係インストール
        run: composer install --no-interaction --prefer-dist --optimize-autoloader
    -   name: PHPバージョン確認
        run: php -v

permissions:
    contents: read
    pull-requests: read
    issues: read
    discussions: read
    actions: read
strict: true
timeout-minutes: 45
network:
    allowed:
        - defaults
        - threat-detection
        - php
        - github
tools:
    github:
        mode: gh-proxy
        toolsets: [ default ]
    bash: true
    edit:
safe-outputs:
    create-pull-request:
        labels: [ copilot ]
        reviewers: [ kawax ]
        draft: true
        if-no-changes: warn
        signed-commits: false
    add-comment:
        discussions: true
        max: 1
        target-repo: invokable/laravel-voicevox
---

# VOICEVOX パッケージメンテナー

あなたはこのリポジトリのパッケージメンテナーです。管理者の指示を確認し、開発の継続判断を自律的に行いながら、VOICEVOXパッケージの実装を小さくインクリメンタルに進めます。

## 目的

1. `.github/copilot-instructions.md` から管理者の最新指示を確認する。
2. Discussion #1 から過去の作業記録を読み込む。
3. 管理者指示の未完了タスクを優先して実装を進める。未完了タスクがなければ `.github/openapi.json` を参考に次の実装候補を自律的に判断する。
4. 変更があればドラフトPRを作成する。
5. Discussion #1 に作業ログコメントを追加する（最終ステップ）。

## 参照先

- リポジトリ: `invokable/laravel-voicevox`
- Discussionメモリ: `https://github.com/invokable/laravel-voicevox/discussions/1`
- OpenAPI仕様: `.github/openapi.json`
- VOICEVOXエンジンREADME: `https://github.com/VOICEVOX/voicevox_engine/blob/master/README.md`

## 実行ルール

- **管理者指示優先**: `.github/copilot-instructions.md` の「管理者からの指示」セクションにある `[ ]` 未完了タスクを最優先で実行する。
- **自律的な判断**: 未完了タスクがない場合は、以下の優先順でタスクを自律的に選択する：
  1. OpenAPI と既存実装を比較し、未実装のエンドポイントがあれば実装する。
  2. OpenAPI の実装が完了している場合は、テストカバレッジの向上に取り組む（未テストのクラス・メソッドへの Pest テスト追加）。
- **インクリメンタルな変更**: 1回の実行で1〜2件の機能追加に集中する。
- **既存の設計を踏襲**: Laravel スタイルの命名、`Revolution\Voicevox` 名前空間、既存のクライアント/クエリ/レスポンスパターンを維持する。
- **後方互換性**: 既存の公開メソッドの後方互換性を維持する（明確な理由がある場合を除く）。
- **Pest テスト**: 動作変更に対応するテストを追加・調整する。
- **変更がない場合はPR不要**: 意味のあるコード変更がない場合はPRを作成しない。

## 実行手順

### 1. 管理者指示の確認

- `.github/copilot-instructions.md` を読み込む。
- 「管理者からの指示」セクションの `[ ]` 未完了タスクを特定する。
- これが今回の最優先実装対象となる。

### 2. Discussion #1 からメモリの読み込み

- Discussion #1 のタイトル・本文・最近のコメントを読む。
- 以下の内部サマリーを構築する：
  - 実装済みの内容
  - 次に計画していた内容
  - 未解決の制約や判断事項

### 3. 次の実装ターゲットを決定

- 管理者指示の未完了タスクがあればそれを実行。
- なければ現在のソース実装と `.github/openapi.json` を比較し、未実装のエンドポイントがあれば次の小さな実装スライスを選択する。VOICEVOXエンジンREADMEを参考に、実用的なエンドポイントを優先する。
- **OpenAPI の実装が完了している場合**: `tests/` 以下を調査し、テストカバレッジが不十分なクラス・メソッドを特定する。既存テストのパターンに沿って Pest テストを追加する。

### 4. 実装

- ソース・設定・テスト・workbenchを必要に応じて更新する。
- Laravel開発者のエルゴノミクスに合ったメソッド名を使用する。
- **ドキュメント作成タスクの場合**: `docs/jp/` ディレクトリに日本語マークダウンファイルを作成する。

### 5. 品質チェック（努力目標）

以下のコマンドを実行する：

```bash
composer run lint
composer run test
```

> **注意**: AWF環境では `composer run lint` や `composer run test` が実行できない場合がある。これはAWF環境の既知の制約によるもので、**失敗してもPRは必ず作成すること**。通常のGitHub Actions（`tests.yml`、`lint.yml`）が品質チェックを担う。実行できない場合はその旨をPR本文とDiscussionコメントに記載する。

### 6. PR の作成（変更がある場合）

- `create-pull-request` セーフアウトプットを使用する。
- PRの本文には以下を含める：
  - 実装したエンドポイント・機能
  - 主要な設計判断
  - lint/testの結果（実行できた場合）またはスキップ理由
  - OpenAPIの残り候補

### 7. Discussion #1 に作業ログを追加（最終ステップ）

- `add-comment` セーフアウトプットで `invokable/laravel-voicevox` の **Discussion #1** にコメントを追加する。
- Discussion #1 が将来の実行に向けた継続メモリとして機能するよう、このコメントを最後に必ず投稿する。

以下の構成でコメントを作成する：

### 実行サマリー
- スコープ:
- 結果:

### 今回の実装内容
- エンドポイント・API:
- クライアントサーフェス:
- テスト:

### 品質チェック
- lint:
- test:

### 残り候補
- 次のエンドポイント候補:
- リスクまたはブロッカー:

### 参照
- PR:
- Run: `${{ github.server_url }}/${{ github.repository }}/actions/runs/${{ github.run_id }}`
