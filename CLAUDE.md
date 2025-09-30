# wp-hamazon - Claude Code 作業用ドキュメント

## プロジェクト概要

WordPress.orgで公開されているアフィリエイトプラグイン。Amazon PA-APIやDMM APIと連携して商品情報を取得・表示する。

- **メインブランチ**: `master`
- **WordPress.org**: https://wordpress.org/plugins/wp-hamazon/
- **GitHub**: https://github.com/hametuha/wp-hamazon

## 技術スタック

- **PHP**: 7.4以上（Composer管理）
- **Node.js**: 18.20.8（volta管理）
- **WordPress**: 5.6以上（現在6.8までテスト済み）
- **開発環境**: wp-env（Docker Desktop for MacOS）

## ディレクトリ構造

```
wp-hamazon/
├── app/                          # PHPソースコード
│   └── Hametuha/WpHamazon/      # メイン名前空間
│       ├── BootStrap.php         # プラグイン初期化・アセット登録
│       ├── BlockEditor.php       # ブロックエディター対応
│       └── Service/              # 外部API連携
│           ├── Amazon.php        # Amazon PA-API
│           └── Dmm.php          # DMM API
├── src/                          # フロントエンドソース
│   ├── js/                       # JavaScript/React
│   └── scss/                     # スタイルシート
├── assets/                       # ビルド済みアセット（gitignore）
├── bin/                          # ビルドスクリプト
│   └── build.sh                 # リリース用ビルドスクリプト
├── .github/workflows/           # GitHub Actions
└── wp-hamazon.php              # プラグインメインファイル

```

## 開発コマンド

### 環境管理

```bash
# wp-env起動
npm start

# wp-env停止
npm stop

# WordPressコンテナでWP-CLIを実行
npm run cli -- [command]

# テスト環境でWP-CLI実行
npm run cli:test -- [command]
```

### ビルド・監視

```bash
# 本番用ビルド
npm run package

# 開発用監視（自動ビルド）
npm run watch

# Gulpタスク実行
npm run gulp [task]
```

### リンティング

```bash
# CSS構文チェック（自動修正付き）
npm run lint:css

# JavaScript構文チェック
npm run gulp eslint

# JavaScript自動修正
npm run fix:js

# PHP構文チェック
composer lint
```

### コミット

```bash
# Claudeとの共著コミット（Co-authored-by自動付与）
git cc-commit "コミットメッセージ"

# 通常のコミット
git commit -m "メッセージ"
```

**注意**: `git cc-commit` はエイリアスで、コミットメッセージの末尾に `Co-authored-by: Claude <noreply@anthropic.com>` が自動追加されます。

### GitHub CLI

```bash
# イシュー・プルリクエスト作成
gh issue create
gh pr create

# リリース管理
gh release list
gh release view [tag]
```

## Pre-commit フック

Huskyにより、コミット前に以下が自動実行されます：

1. `npm run lint:css` - CSS構文チェック
2. `npm run gulp eslint` - JavaScript構文チェック
3. `composer lint` - PHP構文チェック

いずれかが失敗するとコミットがブロックされます。

## リリースフロー

### 自動化された部分

1. **masterへのマージ** → `release-drafter.yml` が Draft Release を自動作成
2. **GitHub UIでReleaseをパブリッシュ**（タグも同時に作成） → fumikitoさんの権限で実行
3. **タグプッシュ** → `wordpress.yml` がWordPress.orgへ自動デプロイ

### ワークフロー詳細

- **release-drafter.yml**: masterにマージされるたびにDraft Releaseを更新
- **wordpress.yml**: `v*` タグがプッシュされたときにWordPress.orgへデプロイ
- **wp-outdated.yml**: 月次でWordPressバージョン互換性をチェック
- **test.yml**: PRやmasterへのプッシュ時にPHPUnit・リンター実行

### 必要なGitHub Secrets

- `WPORG_FUMIKI_USERNAME`: WordPress.orgのユーザー名
- `WPORG_FUMIKI_PASSWORD`: WordPress.orgのアプリケーションパスワード

### 環境保護設定

- **production環境**: タグ `v*.*.*` のみデプロイ可能
- **タグ保護ルール**: バージョンタグ（`v*.*.*` / `*.*.*`）の作成・削除を制限
  - Organization AdminとMaintainロールはバイパス可能

## ビルドプロセス

`bin/build.sh [tag]` で実行：

1. タグ名から `v` プレフィックスを削除してバージョン抽出
2. Composer依存関係インストール（`--no-dev`）
3. NPM依存関係インストール
4. `npm run package` でアセットビルド
5. WordPress.orgへのデプロイ準備完了

## 翻訳

- **テキストドメイン**: `hamazon`
- **読み込みタイミング**: `init` アクション（WordPress 6.7.0+対応）
- **ファイル**: `languages/` ディレクトリ

## WordPress統合

### 対応エディター

- **クラシックエディター**: TinyMCEボタンで商品検索モーダル起動
- **ブロックエディター**: カスタムブロックで商品挿入

### REST API

- エンドポイント: `/hamazon/v3/{service}`
- 対応サービス: Amazon PA-API、DMM API、楽天など

### 主要フック

- `init` (priority 10): 翻訳読み込み・Bootstrap初期化
- `init` (priority 20): アセット登録（`BootStrap::register_assets()`）
- `init` (priority 30): ブロック登録（`BlockEditor::register_blocks()`）

**重要**: フック優先度の順序が重要。`wp_localize_script()` はスクリプト登録後に呼ぶ必要があるため、BlockEditorはBootStrapより後に実行。

## 既知の課題

### PHPCompatibility

`composer lint` で使用する `PHPCompatibility` ルールは、PHP 8.x環境でエラーを起こすため、`phpcs.ruleset.xml` で一時的に無効化しています。

```xml
<!-- TODO: PHPCompatibility 9.3.x doesn't work with PHP 8.x -->
<exclude name="PHPCompatibility"/>
```

### Husky v10非推奨警告

`.husky/pre-commit` の以下の行はHusky v10で削除が必要になります：

```sh
#!/usr/bin/env sh
. "$(dirname -- "$0")/_/husky.sh"
```

現在は正常に動作していますが、将来のアップグレード時に対応が必要です。

## トラブルシューティング

### Amazon PA-API 429エラー

Too Many Requestsは正常な動作です。同一アカウント（4サイト程度）で共有している場合、レート制限に達しやすくなります。

### DMM API null エラー

検索結果が空の場合、`$search_result->result->items` が `null` になる可能性があります。`Dmm.php` では `isset()` と `is_array()` でチェック済み。

### Classic Editorボタンが表示されない

`wp_localize_script('hamazon-editor', 'HamazonEditor', ...)` が正しく実行されているか確認。スクリプトが登録される前に `wp_localize_script()` を呼ぶと失敗します。

### Block Editorが動かない

`BlockEditor::register_blocks()` の優先度が `BootStrap::register_assets()` より前になっていないか確認。現在は priority 30 で実行されています。

## コーディング規約

- **PHP**: WordPress Coding Standards + PHPCS
- **JavaScript**: WordPress ESLint Plugin
- **CSS/SCSS**: WordPress Stylelint Config
  - 名前付き色は使用不可（16進数で記述）
  - フォントファミリーには必ずフォールバックを指定
  - CSS詳細度の順序に注意

## 参考リンク

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Amazon Product Advertising API](https://webservices.amazon.com/paapi5/documentation/)
- [DMM Affiliate API](https://affiliate.dmm.com/api/)
- [wp-env Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)