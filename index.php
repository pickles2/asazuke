<?php
chdir(__DIR__);
ini_set('memory_limit', -1); // 無制限
gc_enable(); // GC有効

$D = dirname(__FILE__);
require_once ($D . '/vendor/autoload.php');

use Mshiba\Px2lib\Asazuke;
use Mshiba\Px2lib\Asazuke\AsazukeConf;
use Mshiba\Px2lib\Asazuke\AsazukeUtil;

// 初期化処理 フォルダ作成
$init = new Asazuke\Asazuke();
unset($init);

// ビルドインサーバー起動
if (! (version_compare(PHP_VERSION, '5.4.0') >= 0)) {
    echo 'php version >= 5.4.0 needed';
    exit(1);
}
// windows: tasklist /fi "imagename eq postgres.exe" | findstr "検索文字列"
// $cmd = 'php -S ' . AsazukeConf::$buildInServerIp . ':' . AsazukeConf::$buildInServerPort . ' -t src/data/SampleSite/ router.php';
// $procCheckCmd = 'ps aux | grep php';
// exec($procCheckCmd, $arr, $res);
//
// $hasProc = false;
// $grepResult = preg_grep("#" . $cmd . "#", $arr);
// if (count($grepResult) > 0) {
//     $hasProc = true;
// }
// // サブプロセスで実行 @see http://goo.gl/zcZSUA
// if (! $hasProc) {
//     $wrapCmd = $cmd . ' > /dev/null 2>&1 &';
//     exec($wrapCmd, $arr, $res);
//     if ($res === 0) {
//         // echo "sarvice started." . "\n";
//     }
// } else {
//     // echo "already started." . "\n";
// }

// メイン処理
if (count($argv) >= 2) {

    if ($argv[1] === 'inline') {
        echo "inline css作成";
        $AsazukeInlineCSS = new Asazuke\AsazukeInlineCSS();
        exit(0);
    }

    if ($argv[1] === 'conf-json') {
        // configをjson形式で取得
        echo (json_encode(Asazuke\AsazukeConf::getProps(), JSON_UNESCAPED_UNICODE));
        exit(0);
    }

    if ($argv[1] === 'concatcss') {
        // php index.php resolve
        // css解決
        $Asazuke = new Asazuke\Asazuke();
        $AsazukeDB = new Asazuke\AsazukeDB();

        $matches = AsazukeUtil::getFileList(dirname(Asazuke\AsazukeConf::getCss()) . '/', '*.html');
        // echo $matches[0];
        var_dump($Asazuke->concatcss($matches[0]));

        // foreach ($matches as $aryIndex => $path) {
        // $FileId=($aryIndex + 1);
        // $recoad = $AsazukeDB->selectAsazuke('id='.$FileId);
        // $relativePath = $recoad[0]['filePath'];
        // $copyHtml = $Asazuke->pathResolve($matches, $aryIndex, $relativePath);
        // $cssPath = AsazukeUtil::getDatPath($FileId, Asazuke\AsazukeConf::$css);
        // file_put_contents($cssPath, $copyHtml);
        // }
        exit(0);
    }
    if ($argv[1] === 'cssworks') {
        // php index.php resolve
        // htmlファイルのパス解決
        $Asazuke = new Asazuke\Asazuke();
        $AsazukeDB = new Asazuke\AsazukeDB();

        $matches = AsazukeUtil::getFileList(dirname(Asazuke\AsazukeConf::getHtml()) . '/', '*.html');

        $len = count($matches);
        foreach ($matches as $index => $path) {
            try {
                $id = basename($path, '.html');
                if(Asazuke\AsazukeConf::$ctrlCd){
                  echo "\r\033[K" . $id . ".htmlを処理中 " . ($index + 1) . "/" . $len; // ESC[K カーソル位置から行末までをクリア
                }else{
                  echo $id . ".htmlを処理中 " . ($index + 1) . "/" . $len . "\n";
                }
                $recoad = $AsazukeDB->selectAsazuke('id=' . $id);
                $cssPath = Asazuke\AsazukeUtil::getDatPath($id, Asazuke\AsazukeConf::getCss());
                $relativePath = $recoad[0]['filePath'];

                $copyHtml = $Asazuke->pathResolve($matches, $index, $relativePath);
            } catch (Exception $e) {
                $copyHtml = $e;
            }
            file_put_contents($cssPath, $copyHtml);
        }
        echo "Finished!! (HTMLダウンロード)";
        echo str_repeat("\n", 4);
        exit(0);
    }

    if ($argv[1] === 'rm-cssworks') {
        // php index.php rm-dat
        // "cssworks/*.html"ファイルを削除
        $matches = AsazukeUtil::getFileList(dirname(Asazuke\AsazukeConf::getCss()) . '/', '*');
        print_r($matches);
        foreach ($matches as $file) {
            unlink($file);
        }
        echo "Remove complete.\n";
        exit(0);
    }
    if ($argv[1] === 'rm-dat') {
        // php index.php rm-dat
        // "*.dat"ファイルを削除
        $matches = AsazukeUtil::getFileList(dirname(Asazuke\AsazukeConf::getDat()) . '/', '*.dat');
        print_r($matches);
        foreach ($matches as $file) {
            unlink($file);
        }
        echo "Remove complete.\n";
        exit(0);
    }
    if ($argv[1] === 'rm-html') {
        // php index.php rm-html
        // "htmlCache/*.html"ファイルを削除
        $matches = AsazukeUtil::getFileList(dirname(Asazuke\AsazukeConf::getHtml()) . '/', '*.html');
        print_r($matches);
        foreach ($matches as $file) {
            unlink($file);
        }
        echo "Remove complete.\n";
        exit(0);
    }

    if ($argv[1] === 'site-validation') {
        // php index.csv site-validation

        // SSデータ→サイト検証を行う

        $Asazuke = new Asazuke\Asazuke();
        $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
        // 表の初期化
        $AsazukeSiteScanDB->truncate('t_asazuke');
        $result = $AsazukeSiteScanDB->select();
        //$result = $AsazukeSiteScanDB->select('id <> 548');// が処理できない 除外する
        // $result = $AsazukeSiteScanDB->select('id <> 538');
        $aryCsv = array();
        foreach ($result as $value) {
            $aryCsvRow = array();
            $aryCsvRow[$Asazuke->csvColmns[0]] = $value['id'];
            $aryCsvRow[$Asazuke->csvColmns[1]] = $value['fullPath'];
            $aryCsv[] = $aryCsvRow;
        }
        $Asazuke->exec($aryCsv);
        exit(0);
    }
    if ($argv[1] === 'site-validation-show') {
        // php index.php site-validation-show
        // サイト検証を行ったデータを標準出力
        $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
        $result = $AsazukeSiteScanDB->selectAsazuke();
        var_dump($result);
        exit(0);
    }
    if ($argv[1] === 'site-validation-json') {
        // php index.php site-validation-show
        // サイト検証を行ったデータを標準出力
        $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
        $result = $AsazukeSiteScanDB->selectAsazuke("1=1 ORDER BY filePath");
        $filter_ary =  array();
        foreach($result AS $row_data){
          $filter_ary[] = array($row_data["id"], $row_data["filePath"], $row_data["errorCount"], $row_data["warningCount"]);
        }
        echo (json_encode($filter_ary, JSON_UNESCAPED_UNICODE));
        exit(0);
    }
    if ($argv[1] === 'rm-scraping') {
        // php index.php rm-scraping
        // "scraping/*.html"ファイルを削除
        $exts = array(
            // "*.svg",
            // "*.jpeg",
            // "*.jpg",
            // "*.gif",
            // "*.png",
            // "*.htm",
            // "*.html",
            "*"
        );
        foreach ($exts as $ext) {
            $matches = AsazukeUtil::getFileList(dirname(Asazuke\AsazukeConf::getScrapingHtml()) . '/', $ext);
            print_r($matches);
            foreach ($matches as $file) {
                unlink($file);
            }
        }

        echo "Remove complete.\n";
        exit(0);
    }
    // desktop.sqlに書かれたSQLを実行
    if ($argv[1] === 'file-sql') {
        // php index.php scraping
        // サイト検証を行ったデータをCSV出力
        $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
        $sqlData = file_get_contents($D.'/desktop.sql');
        // /^\s*\-\-.*$/g' <- Javascript
        $sqlData2 = preg_replace('/\s*\-\-.*/u', '', $sqlData);
        // var_dump(($sqlData2));

        $onelineSQL = implode(";\n",(explode(';', AsazukeUtil::stripReturn($sqlData2, ' '))));
        $ary = AsazukeUtil::str2array($onelineSQL);
        foreach ($ary as $key => $sql) {
          if($sql === 'commit;' || $sql === ''){
            // PDOで経由で実行している為,commitが不要。
          }else{
            echo $sql. "\n";
            $result = $AsazukeSiteScanDB->query($sql);
            foreach ($result as $key => $val) {
                echo "-> ".AsazukeUtil::stripReturn(print_r($val,TRUE)). "\n";
            }
          }
        }
        exit(0);
    }
    if ($argv[1] === 'file-sql-json') {
        // php index.php scraping
        // サイト検証を行ったデータをCSV出力
        $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
        $sqlData = file_get_contents($D.'/desktop.sql');
        // /^\s*\-\-.*$/g' <- Javascript
        $sqlData2 = preg_replace('/\s*\-\-.*/u', '', $sqlData);
        // var_dump(($sqlData2));

        $onelineSQL = implode(";\n",(explode(';', AsazukeUtil::stripReturn($sqlData2, ' '))));
        $ary = AsazukeUtil::str2array($onelineSQL);
        foreach ($ary as $key => $sql) {
          if($sql === 'commit;' || $sql === ''){
            // PDOで経由で実行している為,commitが不要。
          }else{
            echo $sql. "\n";
            // SELECT文飲み
            if(preg_match('/select/i', $sql, $match)){
              // $result = $AsazukeSiteScanDB->query($sql);
              // カラム名で結果取得
              $result = $AsazukeSiteScanDB->query($sql, \PDO::FETCH_ASSOC);
              echo "Result -> ".json_encode($result, JSON_UNESCAPED_UNICODE). "\n";
              // foreach ($result as $key => $val) {
              //     echo "Result -> ".json_encode($val, JSON_UNESCAPED_UNICODE). "\n";
              // }
            }else{
              echo "SELECT文以外は実行出来ません。\n";
            }
          }
        }
        exit(0);
    }

    if ($argv[1] === 'scraping') {
        // php index.php scraping
        // サイト検証を行ったデータをCSV出力
        $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
        $result = $AsazukeSiteScanDB->selectAsazuke();

        $Asazuke = new Asazuke\Asazuke();
        $Asazuke->scrapingHTML($result);

        // スクリプトコピー
        $targetDir = Asazuke\AsazukeConf::getScripstDir() . '/';
        $distDir = Asazuke\AsazukeConf::getExpHtdocs() . '/';
        if ($handle = opendir($targetDir)) {
            /* ディレクトリをループする際の正しい方法です */
            while (false !== ($entry = readdir($handle))) {
                $file = $targetDir . $entry;
                $dist = $distDir . $entry;
                if (is_file($file)) {
                    // echo "$file\n";
                    copy($file, $dist);
                }
            }
            closedir($handle);
        }

        // ブラウザで開く
        // $cmd = 'open -b com.google.Chrome http://' . AsazukeConf::$buildInServerIp . ':' . AsazukeConf::$buildInServerPort . '/mst.php';
        // exec($cmd, $arr, $res);
        //echo "Finished."."\n";
        echo "Finished!! (サイトマップCSV作成)";
        exit(0);
    }
    if ($argv[1] === 'site-validation-csv') {

        // php index.php site-validation-csv
        // サイト検証を行ったデータをCSV出力
        $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
        $result = $AsazukeSiteScanDB->selectAsazuke("1=1 ORDER BY filePath");

        $Asazuke = new Asazuke\Asazuke();
        $Asazuke->createPx2CSV($result);
        //echo "Finished!! (サイトマップCSV作成)";
        exit(0);
    }
    if ($argv[1] === 'site-validation-result') {
        // php index.php site-validation-result
        // サイト検証を行ったデータを確認
        $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
        $result = $AsazukeSiteScanDB->selectAsazuke();
        {
            $jobTitle = 'LintResult';
            // /////////////////
            date_default_timezone_set('Asia/Tokyo');
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()
                ->setCreator("")
                ->setLastModifiedBy("")
                ->setTitle("")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");

            $data = array();
            // 列名
            $data[] = array(
                'path',
                'Error',
                'Warning',
                'URL'
            );
            foreach ($result as $rowData) {
                $data[] = array(
                    $rowData['filePath'],
                    $rowData['errorCount'],
                    $rowData['warningCount'],
                    'http://' . AsazukeConf::$buildInServerIp . ':' . AsazukeConf::$buildInServerPort . '/lintResult.php?path=' . urldecode($rowData['filePath'])
                );
            }
            // A1を基準にデータ流し込み
            $objPHPExcel->setActiveSheetIndex(0)->fromArray($data, null, 'A1');

            // リンク挿入
            $aryIndex = array_search('url', $data[0]);
            $column = chr(ord("A") + $aryIndex);
            $worksheet = $objPHPExcel->setActiveSheetIndex(0);
            $lastRow = $worksheet->getHighestRow();
            $rowOffset = 1;
            for ($row = 1 + $rowOffset; $row <= $lastRow; $row ++) {
                $cell = $worksheet->getCell($column . $row);
                $cell->getHyperlink()->setUrl('http://' . AsazukeConf::$buildInServerIp . ':' . AsazukeConf::$buildInServerPort . ' /lintResult.php?path=' . urldecode($cell->getValue()));
                $cell->getStyle()
                    ->getFont()
                    ->getColor()
                    ->setARGB(PHPExcel_Style_Color::COLOR_BLUE);
            }
            $objPHPExcel->getActiveSheet()->setTitle($jobTitle);
            $objPHPExcel->setActiveSheetIndex(0);
            // Save Excel 2007 file
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $xlsxfile = __DIR__ . '/' . $jobTitle . date("md_His") . '.xlsx';
            $objWriter->save($xlsxfile);
            echo <<< EOL

[export csv]
$xlsxfile


EOL;

            // 出力データ確認
            // $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
            // print_r($sheetData);
            // /////////////////
        }
        exit(0);
    }
    if ($argv[1] === 'site-validation-csv-origin') {
        // php index.php site-validation-csv
        // サイト検証を行ったデータをCSV出力
        $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
        $result = $AsazukeSiteScanDB->selectAsazuke();

        AsazukeUtil::createCSV($result);
        exit(0);
    }
    if ($argv[1] === 'site-scan') {
        // php index.php sitescan
        date_default_timezone_set(Asazuke\AsazukeConf::$timezone);
        $db_file = Asazuke\AsazukeConf::getDbFile();
        $dt = date("md_His");
        rename($db_file, $db_file . ".${dt}");

        $AsazukeSiteScan = new Asazuke\AsazukeSiteScan();
        $AsazukeSiteScan->exec();
        echo "Finished!! (サイトスキャン)";
        exit(0);
    }
    if ($argv[1] === 'csv') {
        if (count($argv) >= 3) {
            // php index.php csv <depth>
            csv($argv[2]);
            exit(0);
        } else {
            // php index.php show
            csv();
            exit(0);
        }
    }

    if ($argv[1] === 'show') {
        if (count($argv) >= 3) {
            // php index.php show <depth>
            show($argv[2]);
            exit(0);
        } else {
            // php index.php show
            show();
            exit(0);
        }
    } elseif ($argv[1] === 'tables' || $argv[1] === 'table') {
        // php index2.php tables
        tables();
        exit(0);
    } elseif ($argv[1] === 'schema' || $argv[1] === 'create') {
        if (count($argv) >= 3) {
            // php index.php schema <table_name>
            schema($argv[2]);
            exit(0);
        } else {
            // php index.php schema
            schema();
            exit(0);
        }
    }
} else {

    {
        echo "\n" . "【Debug】" . "\n";
        echo "\n\n" . "[get_include_path]" . "\n";
        print_r(get_include_path());
        // echo "\n\n" . "[get_included_files]" . "\n";
        // print_r(get_included_files());
        echo "\n\n" . "[conf]" . "\n";
        print_r(Asazuke\AsazukeConf::getProps());
    }
    {
        echo "\n" . "【Usage】" . "\n";
        // echo "\n"."[get_include_path]"."\n";
    }
    $ary = json_decode(file_get_contents('composer.json'), true);
    echo "\n\n" . "[composerコマンド一覧]" . "\n";
    foreach ($ary['scripts'] as $key => $val) {
        echo "  $ composer " . $key . "\n";
    }
    echo <<< EOL

'Asazuke'で使える、'composer'の詳しい説明は下記のリンクをご参照して下さい。
```
https://github.com/Misaki-Shibata/asazuke#その他コマンド
```


EOL;
}

function query()
{
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $a = $AsazukeSiteScanDB->tables();
    var_dump($a);
}

function tables()
{
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $a = $AsazukeSiteScanDB->tables();

    $table = new Zend\Text\Table\Table(array(
        'columnWidths' => array(
            20
        )
    ));
    $table->appendRow(array(
        "tbl_name"
    ));

    foreach ($a as $v) {
        $row = new Zend\Text\Table\Row();
        $row->appendColumn(new Zend\Text\Table\Column($v['tbl_name']));
        $table->appendRow($row);
    }
    echo $table;
}

function schema($table_name = "")
{
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $a = $AsazukeSiteScanDB->schema($table_name);

    $table = new Zend\Text\Table\Table(array(
        'columnWidths' => array(
            100
        )
    ));
    $table->appendRow(array(
        "sql"
    ));

    foreach ($a as $v) {
        $row = new Zend\Text\Table\Row();
        $row->appendColumn(new Zend\Text\Table\Column($v['sql']));
        $table->appendRow($row);
    }
    echo $table;
}

function show($depth = "")
{
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();

    if ($depth !== '') {
        $a = $AsazukeSiteScanDB->selectXth(intval($depth));
        AsazukeUtil::createTable($a);
    } else {
        $a = $AsazukeSiteScanDB->select();
        AsazukeUtil::createTable($a);
    }
}

/**
 * CSV出力
 *
 * @param string $depth
 */
function csv($depth = "")
{
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();

    if ($depth !== '') {
        $a = $AsazukeSiteScanDB->selectXth(intval($depth));
        AsazukeUtil::createCSV($a);
    } else {
        $a = $AsazukeSiteScanDB->select();
        AsazukeUtil::createCSV($a);
    }
}

/**
 * Scrapingしたhtml
 *
 * @param string $depth
 */
function scrapingHtml($depth = "")
{
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();

    if ($depth !== '') {
        $a = $AsazukeSiteScanDB->selectXth(intval($depth));
        AsazukeUtil::createCSV($a);
    } else {
        $a = $AsazukeSiteScanDB->select();
        AsazukeUtil::createCSV($a);
    }
}
