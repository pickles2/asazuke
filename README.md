Asazuke
=========

Asazuke は、Pickles 2の支援ツールです。

- サイトのマップの作成
- 旧サイトのHtmlのLintを行う。
- HTMLのモジュール作成機能（作成予定）

## インストール手順 - Install

Asazuke のインストールは、`composer` コマンドを使用します。

```
$ git clone https://github.com/misak1/m-asazuke.git
$ cd asazuke
$ composer update
$ composer chmod
```

## 設定変更
```
$ vim src/AsazukeConf.php
```
-サイト解析を行うurlを変更して下さい。

## 実行手順 - run
```
$ composer run
```

## その他コマンド
|コマンド|コマンド説明|
|---|---|
|$ composer darwin-chmod|sqliteを使う為に書込権限の変更|
|$ composer win32-copy-conf|設定ファイルコピー(Windows)|
|$ composer run|設定を表示|
|$ composer run:file-sql|SQLファイルの実行|
|$ composer run:file-sql-json|SQLファイルの実行、結果をjsonで出力|
|$ composer run:conf-json|設定をjson形式で出力|
|$ composer run:site-scan|ディレクトリツリーを作成|
|$ composer run:csv <数値>|CSV出力、<数値>に1が指定された場合't_1st'をCSV出力|
|$ composer run:show <数値>|標準出力、<数値>に1が指定された場合't_1st'を標準出力|
|$ composer run:table|テーブル一覧を表示|
|$ composer run:schema <テーブル名>|CREATE文を標準出力、<テーブル名>に指定した場合テーブルのCREATE文を表示|
|$ composer run:rm-dat|*.datを削除|
|$ composer run:rm-html|*.htmlを削除|
|$ composer run:site-validation|sitescanデータを元にリモートサイト解析、素のhtmlをダウンロード|
|$ composer run:site-validation-show|解析データを表示|
|$ composer run:site-validation-json|解析データをJSONで表示（デスクトップ版用）|
|$ composer run:site-validation-csv|解析データをCSV出力(Pixles2 サイトマップCSV形式)|
|$ composer run:site-validation-csv-origin|解析データをCSV出力|
|$ composer run:site-validation-result|解析データの確認用に|
|$ composer run:rm-scraping|HTML切り出しデータの削除|
|$ composer run:scraping|HTML切り出し|
|$ composer run:rm-cssworks|URL補完データ削除|
|$ composer run:cssworks|URL補完（リンクをhttp://〜形式に変更）|
|$ composer run:inline| CSSをインライン化（duplicated）｜
|$ composer test|ユニットテストを実行|
|$ sudo php -S localhost:8899 -t src/data/SampleSite src/data/SampleSite/router.php|作成したSampleSiteをサーバー上で確認|


フォルダ構造

```
index.php エントリーポイント
src
├── Asazuke.php　サイト解析、エラーチェック、コンテンツ取得、　サイトマップCSV作成用
├── AsazukeConf.php　設定ファイル
├── AsazukeDB.php　DB（SQLite）接続＆SQL作成
├── AsazukeInlineCSS.php　CSSインライン化
├── AsazukeMessage.php　Asazuke内で使うテキスト、エラーコードなど
├── AsazukeSiteScan.php　　サイトスキャン
├── AsazukeUtil.php　ユーティリティ
├── AsazukeUtilConsole.php　ターミナル関連のユーティリティ
├── AsazukeUtilFile.php　ファイル操作のユーティリティ
├── data
│   ├── SampleSite　
│   ├── SampleSite-testParam
│   ├── asazuke.log
│   ├── asazuke.sqlite
│   ├── cssWorks
│   ├── htmlCache
│   ├── inlineCSS
│   ├── lintResult
│   ├── scraping
│   └── scripts
└── libs
    └── phpQuery-onefile.php

10 directories, 12 files
```
