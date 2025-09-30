# Hamazon

Contributors: Takahashi_Fumiki, hametuha
Tags: amazon, affiliate, dmm, phg
Tested up to: 6.8  
Stable Tag: nightly  

You can add affiliate link in post content via Amazon, iTunes, DMM.

##  Description

You can add affiliate link in visual editor button.
Search on editor and add it immediately.

### Available Services

* Amazon Advertising API(v5)
* PHG iTunes affiliate
* DMM affiliate

### Deprecated

These services below are deprecated.

* Rakuten
* Linkshare

##  Installation

1. Upload `wp-hamazon` folder in `/wp-content/plugins/` directory.
1. Activate plugin.
1. Go to `Setting > Affiliate Setting` and enter credentials.

##  Screenshots

1. You can search affiliate like this screen.

##  Changelog

### 5.1.4

* Fix bug on classic editor.
* Avoid translation error warning.

### 5.1.3

* Fix fatal error on deploy script.

### 5.1.0

* Fix bug with serverside render.

### 5.0.2

* Fix Warning error since 5.4.2

### 5.0.0

**Breaking Changes!**

* Sorry to be late. Followed Amazon Advertising API 5.0. If you use old credentials, go to Amazon Associate Central and get new credentials.
* Markups and API data structures are completely changed, so if you customize with hooks, please check they work as you wanted.
* Add WP CLI command: `wp hamazon search 'science fiction'`

### 4.0.4

* Fix js dependency errors.

### 4.0.3

* Change translation.

### 4.0.2

* Fix JS Error.

### 4.0.1

* Sorry! Fix non gutenberg environment.

### 4.0.0

* Fix PHG bug.
* Add Gutenberg support!

### 3.0.3

* Got [bug report](https://wordpress.org/support/topic/古いバージョンはどこにありますか？/#post-9600252) and remove typehint to avoid fatal error.

### 3.0.0

**BREAKING CHANGE!!**

* All codes are rewriten.
* Drop Rakuten and Linkshare. Shortcodes are now empty.
* Ready for [Shortcake](https://ja.wordpress.org/plugins/shortcode-ui/). Now you can preview shortcode resutl.
* If you feel this version breaks your site, stay old verison or contact me via [support forum]().

### 2.3.1

* 関数`tmkm_amazon_view`が動かなくなっていたので、直しました。
* HTMLリンクを出力する`hamazon_asin_link`を追加しました。

###  2.3

* ショートコード挿入ボタンをつけました。
* ショートコード内の情報を出力できるようにしました。一言コピーのようなものが入れられます。後方互換は取っているので、いままでのコンテンツはそのまま表示されます。
* サービスにPHG（iTuensアフィリエイト）とDMMを追加しました。

```
// これまで
[tmkm-amazon]00000000[/tmkm-amazon]
// これから
[tmkm-amazon asin='00000000']この本は最高ですよ！[/tmkm-amazon]
```

### 2.2

* 楽天をサービスとして追加
* マークアップをドラスティックに変更しました。これまで利用していた方は注意してください。

### 2.1

* リンクシェアのAPIがリクエストを返さないことがあるので、キャッシュ方法を変更

### 2.0

* リンクシェアを追加

### 1.0

* はじめてのリリース
