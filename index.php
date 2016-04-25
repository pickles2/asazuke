<?php
chdir(__DIR__);
ini_set('memory_limit', - 1); // 無制限
set_time_limit(86400);
gc_enable(); // GC有効

$D = dirname(__FILE__);
require_once ($D . '/vendor/autoload.php');

use Mshiba\Px2lib\Asazuke;
use Mshiba\Px2lib\Asazuke\AsazukeConf;
use Mshiba\Px2lib\Asazuke\AsazukeUtil;
include ('task.php');

// 初期化処理 フォルダ作成
$init = new Asazuke\Asazuke();
unset($init);

// メイン処理
// 実行方法
// $ php index.php $argv
if (count($argv) >= 2) {
    if ($argv[1] === 'inline') {
        inline();
        return true;
    }
    if ($argv[1] === 'conf-json') {
        confJson();
        return true;
    }
    if ($argv[1] === 'concatcss') {
        concatcss();
        return true;
    }
    if ($argv[1] === 'cssworks') {
        cssworks();
        return true;
    }
    if ($argv[1] === 'rm-cssworks') {
        rmCssworks();
        return true;
    }
    if ($argv[1] === 'rm-dat') {
        rmDat();
        return true;
    }
    if ($argv[1] === 'rm-html') {
        rmHtml();
        return true;
    }
    if ($argv[1] === 'rm-scraping') {
        rmScraping();
        return true;
    }
    if ($argv[1] === 'scraping') {
        scraping();
        return true;
    }
    if ($argv[1] === 'site-validation-csv') {
        siteValidationCsv();
        return true;
    }
    if ($argv[1] === 'site-validation') {
        siteValidation();
        return true;
    }
    if ($argv[1] === 'site-validation-show') {
        siteValidationShow();
        return true;
    }
    if ($argv[1] === 'site-validation-json') {
        siteValidationJson();
        return true;
    }
    if ($argv[1] === 'site-validation-result') {
        siteValidationResult();
        return true;
    }
    if ($argv[1] === 'site-validation-csv-origin') {
        siteValidationCsvOrigin();
        return true;
    }
    if ($argv[1] === 'file-sql') {
        fileSql();
        return true;
    }
    if ($argv[1] === 'file-sql-json') {
        fileSqlJson();
        return true;
    }
    if ($argv[1] === 'site-scan0') {
        siteScan0();
        return true;
    }
    if ($argv[1] === 'site-scan') {
        siteScan();
        return true;
    }
    if ($argv[1] === 'csv') {
        _csv();
        return true;
    }
    if ($argv[1] === 'show') {
        _show();
        return true;
    } elseif ($argv[1] === 'tables' || $argv[1] === 'table') {
        _tables();
        return true;
    } elseif ($argv[1] === 'schema' || $argv[1] === 'create') {
        _schem();
        return true;
    }
    if ($argv[1] === 'which-php') {
        // PATHが通って要るphpのファイルパスを表示
        echo $_SERVER["_"];
        return true;
    }
    if ($argv[1] === 'darwin-chmod') {
        // 書き込み権限を付与
        if (PHP_OS === "WIN32" || PHP_OS === "WINNT") {
            echo "Windows" . "\n";
        } else {
            echo "OSX or Linux" . "\n";
            $cmds = array(
                'chmod -R 777 src/data',
                'chmod 777 src/Asazuke.log desktop.sql'
            );
            foreach ($cmds as $cmd) {
                exec($cmd, $arr, $res);
                var_dump($arr);
                var_dump($res);
            }
        }
    }
    if ($argv[1] === 'win32-copy-conf') {
        // AsazukeConf.php をシンボリックリンクから実ファイルへ変更
        if (PHP_OS === "WIN32" || PHP_OS === "WINNT") {
            echo "Windows" . "\n";
            $cmds = array(
                'copyConf.bat'
            );
            foreach ($cmds as $cmd) {
                exec($cmd, $arr, $res);
                var_dump($arr);
                var_dump($res);
            }
        } else {
            echo "OSX or Linux" . "\n";
        }
    }
    if ($argv[1] === 'countSiteScan'){
        countSiteScan();
    }
    // 実行する場合ダブルクォートでくくらないとshellファンクションとして認識される
    // $ php index.php "selectSiteScanById()"
    if (preg_match_all('/^selectSiteScanById\(\d.*\)/', $argv[1], $matches)){
       $fnc = $matches[0][0];
       //echo $fnc;
       eval("${fnc};");
    }
    /**
     * composer scriptsを使って順番に処理していた部分
     * windowsでcomposer scripts経由で実行すると複数プロセスIDが振られてしまう問題があった為
     * index.php内で複数の処理を実行するように変更
     */
    if ($argv[1] === 'queue_site-validation') {
        rmDat();
        rmHtml();
        siteValidation();
        return true;
    }
    if ($argv[1] === 'queue_site-validation-ex') {
        rmDat();
        rmHtml();
        siteValidation();
        //rmCssworks();
        //cssworks();
        return true;
    }
    if ($argv[1] === 'queue_scraping') {
        rmScraping();
        scraping();
        return true;
    }
    if ($argv[1] === 'queue_cssworks') {
        rmCssworks();
        cssworks();
        return true;
    }
} else {
    defaultAction();
    return true;
}
