<?php
/**
 * 文言やエラーコード
 */
namespace Mshiba\Px2lib\Asazuke;

class AsazukeMessage
{
    // 通常の例外に対するコード
    public static $CD_JS = '"^javascript"で始まる文字列です。';
    public static $CD_MAILTO = '"^mailto"で始まる文字列です。';
    public static $CD_XXX = 'HTTPステータスコード(undefined)';
    public static $CD_1XX = '1xx Informational 情報';
    public static $CD_2XX = '2xx Success 成功';
    public static $CD_3XX = '3xx Redirection リダイレクション';
    public static $CD_4XX = '4xx Client Error クライアントエラー';
    public static $CD_5XX = '5xx Server Error サーバエラー';
    public static $CD_OTHER = '外部サイト';

    // ページ内のhrefを処理する時に付与するコード
    public static $SKIP_CD01 = '開始ディレクトリ不正';
    public static $SKIP_NOT_FOUND = '404 NOT_FOUND';
    public static $SKIP_Filter = 'Asazukeフィルタ例外';
    public static $SKIP_OTHER = '外部サイト';


    public static $MSG_STATUS_CD = 'HTTPステータスコード';


}
?>
