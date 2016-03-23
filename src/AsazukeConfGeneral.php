<?php
namespace Mshiba\Px2lib\Asazuke;

class AsazukeConfGeneral
{
    public static function getProps()
    {
        $class = new \ReflectionClass('Mshiba\Px2lib\Asazuke\AsazukeConf');
        return $class->getStaticProperties();
    }
    // 制御コード
    public static $ctrlCd = false;
    // Timezone
    public static $timezone = 'Asia/Tokyo';
    // 内蔵サーバー 
    public static $buildInServerIp = '127.0.0.1';
    public static $buildInServerPort = '49150';

    public static $isDebuggable = true;
    public static $isInsistently = false; // 'ディレクトリ名' と 'ディレクトリ名/'を別ものとしてチェックする。検索ヒット数が約２倍に増える、falseの場合はhrefを辿るだけ

    // n回/秒の実行間隔調整 (n=5の場合、1秒間に5回以上実谷しないという上限の設定になるので、増やしたからといって処理がはやくなるわけではありません。)
    public static $execPerSecond = 5;
    // リダイレクトの追いかける上限。リダイレクトループ対策
    public static $retryCount = 10;


    public static $dataDir = '/data/';



    // for Windows
    // public static $csv_format = [
    // "encoding" => "SJIS-win",
    // "linefeed" => "\r\n"
    // ];
    // for Mac
    public static $csv_format = [
        "encoding" => "UTF-8",
        "linefeed" => "\n"
    ];



    // HTML出力
    public static $export_html = [
        [
            'name' => 'outerHTML',
            'selector' => '.contents',
            'scope' => 'outerHTML'
        ]
    ]
    // ,
    // [
    // 'name' => 'innerHTML',
    // 'selector' => '.contents',
    // 'scope' => 'innerHTML'
    // ]
    ;
}
