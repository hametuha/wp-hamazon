# app/ ディレクトリ構成

このディレクトリには、wp-hamazon プラグインの PHP ソースコードが配置されています。

## ディレクトリ構造

```
app/
├── README.md           # このファイル
├── Hametuha/           # プラグイン本体のソースコード
│   └── WpHamazon/
│       ├── BootStrap.php
│       ├── BlockEditor.php
│       ├── Service/
│       ├── Constants/
│       └── ...
└── Amazon/             # Amazon Creators API SDK（外部ライブラリ）
    ├── Configuration.php
    ├── ApiException.php
    └── com/amazon/creators/
        ├── api/
        ├── auth/
        └── model/
```

## Hametuha/WpHamazon

wp-hamazon プラグイン本体のソースコードです。通常の開発・修正対象となります。

- 名前空間: `Hametuha\WpHamazon`
- コーディング規約: WordPress Coding Standards（phpcs.ruleset.xml）

## Amazon/ (Creators API SDK)

Amazon 公式の Creators API PHP SDK です。**このディレクトリは外部ライブラリであり、直接編集しないでください。**

- 名前空間: `Amazon\CreatorsAPI\v1`
- ライセンス: Apache License 2.0
- 公式ドキュメント: https://affiliate-program.amazon.com/creatorsapi
- ダウンロード: https://affiliate-program.amazon.com/creatorsapi/docs/en-us/get-started/using-sdk
- phpcs: 除外対象（phpcs.ruleset.xml で設定済み）

### SDK更新手順

Amazon から新しいバージョンの SDK がリリースされた場合、以下の手順で更新してください。

#### 1. 新しい SDK をダウンロード

Amazon Associates Central > Tools > Creator API からSDKをダウンロードするか、
公式リポジトリから取得してください。

#### 2. 既存の Amazon/ ディレクトリを削除

```bash
rm -rf app/Amazon/
```

#### 3. 新しい SDK の src/ を配置

```bash
mkdir -p app/Amazon/
cp -r /path/to/creatorsapi-php-sdk/src/* app/Amazon/
```

#### 4. 依存関係の確認

新しい SDK の `composer.json` を確認し、依存関係に変更があれば
wp-hamazon の `composer.json` を更新してください。

```bash
# 確認すべき項目
# - require.php のバージョン
# - require.guzzlehttp/guzzle のバージョン
# - require.guzzlehttp/psr7 のバージョン
```

#### 5. Composer の更新

```bash
composer update
```

#### 6. 動作確認

SDK のバージョンアップに伴い、API の呼び出し方法が変更されている可能性があります。
以下のファイルを確認し、必要に応じて修正してください。

- `app/Hametuha/WpHamazon/Constants/AmazonConstants.php`
- `app/Hametuha/WpHamazon/Service/Amazon.php`

### SDK バージョン履歴

| バージョン | 導入日 | 備考 |
|-----------|--------|------|
| 1.1.2 | 2026-01-30 | 初回導入（PA-API v5 から移行） |
