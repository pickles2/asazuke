<?php
namespace Mshiba\Px2lib\Asazuke;

class AsazukeConf extends AsazukeConfCsvCols
{
  // 実行環境設定
  // public static $startPath = '/ja/diary/';
    public static $startPath = '/';

  // 環境設定
  public static $url = 'http://sample.jp';

  // 認証
    public static $authUser = '';
    public static $authPass = '';

  // プロジェクト名 = ディレクトリ名
  public static $projectName = 'sample.jp';

  /**
   * 読み込みサイトマップ
   */
  public static function getCsv()
  {
      return __DIR__ . self::$dataDir .self::$projectName.'/filelist.csv';
  }

  /**
   * Lint結果
   */
  public static function getDat()
  {
      return __DIR__ . self::$dataDir.self::$projectName.'/lintResult/#{ID}.dat';
  }

  /**
   * HTML単純ダウンロード
   */
  public static function getHtml()
  {
      return __DIR__ . self::$dataDir.self::$projectName.'/htmlCache/#{ID}.html';
  }

  /**
   * http解決
   *
   * @return string
   */
  public static function getCss()
  {
      return __DIR__ . self::$dataDir.self::$projectName.'/cssWorks/#{ID}.html';
  }

  /**
   * scraping html解決
   *
   * @return string
   */
  public static function getScrapingHtml()
  {
      return __DIR__ . self::$dataDir.self::$projectName.'/scraping/#{ID}.html';
  }

  /**
   * ログファイル
   *
   * @return string
   */
  public static function getLogPath()
  {
      return __DIR__ . self::$dataDir.self::$projectName.'/asazuke.log';
  }

  /**
   * SQLite3 DBファイル
   */
  public static function getDbFile()
  {
      return __DIR__ . self::$dataDir.self::$projectName.'/asazuke.sqlite';
  }

  /**
   * パーツ出力先
   *
   * @return string
   */
  public static function getExpHtdocs()
  {
      // ディレクトリを指定、*最後にスラッシュを入れない
      return __DIR__ . self::$dataDir.self::$projectName.'/SampleSite';
  }

  /**
   * パーツ出力先
   *
   * @return string
   */
  public static function getScripstDir()
  {
      // ディレクトリを指定、*最後にスラッシュを入れない
      return __DIR__ . self::$dataDir.self::$projectName.'/scripts';
  }
}
