<?php
$D = dirname(__FILE__);
require_once ($D . '/vendor/autoload.php');

use Mshiba\Px2lib\Asazuke;
use Mshiba\Px2lib\Asazuke\AsazukeConf;
use Mshiba\Px2lib\Asazuke\AsazukeUtil;

/**
 * inline css作成
 *
 * @deprecated
 *
 */
function inline()
{
    echo "inline css作成";
    $AsazukeInlineCSS = new Asazuke\AsazukeInlineCSS();
}

/**
 * configをjson形式で出力
 */
function confJson()
{
    echo (json_encode(Asazuke\AsazukeConf::getProps(), JSON_UNESCAPED_UNICODE));
}

/**
 * css解決
 *
 * @deprecated
 *
 */
function concatcss()
{
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
}

/**
 * htmlファイルのパス解決
 * 　パスを全てhttp://形式に補完
 */
function cssworks()
{
    $Asazuke = new Asazuke\Asazuke();
    $AsazukeDB = new Asazuke\AsazukeDB();
    $matches = AsazukeUtil::getFileList(dirname(Asazuke\AsazukeConf::getHtml()) . '/', '*.html');
    $len = count($matches);
    foreach ($matches as $index => $path) {
        try {
            $id = basename($path, '.html');
            if (Asazuke\AsazukeConf::$ctrlCd) {
                echo "\r\033[K" . $id . ".htmlを処理中 " . ($index + 1) . "/" . $len; // ESC[K カーソル位置から行末までをクリア
            } else {
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
    echo "Finished!! (html-download)";
    echo str_repeat("\n", 4);
}

/**
 * "cssworks/*.html"ファイルを削除
 */
function rmCssworks()
{
    $matches = AsazukeUtil::getFileList(dirname(Asazuke\AsazukeConf::getCss()) . '/', '*');
    print_r($matches);
    foreach ($matches as $file) {
        unlink($file);
    }
    echo "Remove complete.\n";
}

/**
 * "*.dat"ファイルを削除
 */
function rmDat()
{
    $matches = AsazukeUtil::getFileList(dirname(Asazuke\AsazukeConf::getDat()) . '/', '*.dat');
    print_r($matches);
    foreach ($matches as $file) {
        unlink($file);
    }
    echo "Remove complete.\n";
}

/**
 * "htmlCache/*.html"ファイルを削除
 */
function rmHtml()
{
    $matches = AsazukeUtil::getFileList(dirname(Asazuke\AsazukeConf::getHtml()) . '/', '*.html');
    print_r($matches);
    foreach ($matches as $file) {
        unlink($file);
    }
    echo "Remove complete.\n";
}

/**
 * サイトスキャンデータを使ってサイト検証を行う
 */
function siteValidation()
{
    $Asazuke = new Asazuke\Asazuke();
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    // 表の初期化
    $AsazukeSiteScanDB->truncate('t_asazuke');
    $result = $AsazukeSiteScanDB->select();
    $aryCsv = array();
    foreach ($result as $value) {
        $aryCsvRow = array();
        $aryCsvRow[$Asazuke->csvColmns[0]] = $value['id'];
        $aryCsvRow[$Asazuke->csvColmns[1]] = $value['fullPath'];
        $aryCsv[] = $aryCsvRow;
    }
    $Asazuke->exec($aryCsv);
}

/**
 * サイト検証を行ったデータを標準出力
 */
function siteValidationShow()
{
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $result = $AsazukeSiteScanDB->selectAsazuke();
    var_dump($result);
}

function siteValidationJson()
{
    // php index.php site-validation-show
    // サイト検証を行ったデータを標準出力
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $result = $AsazukeSiteScanDB->selectAsazuke("1=1 ORDER BY filePath");
    $filter_ary = array();
    foreach ($result as $row_data) {
        $filter_ary[] = array(
            $row_data["id"],
            $row_data["filePath"],
            $row_data["errorCount"],
            $row_data["warningCount"]
        );
    }
    echo (json_encode($filter_ary, JSON_UNESCAPED_UNICODE));
}

/**
 * "scraping/*.html"ファイルを削除
 */
function rmScraping()
{
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
}

/**
 * サイト検証を行ったデータをCSV出力
 * desktop.sqlに書かれたSQLを実行
 */
function fileSql()
{
    $D = dirname(__FILE__);
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $sqlData = file_get_contents($D . '/desktop.sql');
    // /^\s*\-\-.*$/g' <- Javascript
    $sqlData2 = preg_replace('/\s*\-\-.*/u', '', $sqlData);
    // var_dump(($sqlData2));
    
    $onelineSQL = implode(";\n", (explode(';', AsazukeUtil::stripReturn($sqlData2, ' '))));
    $ary = AsazukeUtil::str2array($onelineSQL);
    foreach ($ary as $key => $sql) {
        if ($sql === 'commit;' || $sql === '') {
            // PDOで経由で実行している為,commitが不要。
        } else {
            echo $sql . "\n";
            $result = $AsazukeSiteScanDB->query($sql);
            foreach ($result as $key => $val) {
                echo "-> " . AsazukeUtil::stripReturn(print_r($val, TRUE)) . "\n";
            }
        }
    }
}

/**
 * サイト検証を行ったデータをCSV出力
 */
function fileSqlJson()
{
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $sqlData = file_get_contents($D . '/desktop.sql');
    // /^\s*\-\-.*$/g' <- Javascript
    $sqlData2 = preg_replace('/\s*\-\-.*/u', '', $sqlData);
    // var_dump(($sqlData2));
    $onelineSQL = implode(";\n", (explode(';', AsazukeUtil::stripReturn($sqlData2, ' '))));
    $ary = AsazukeUtil::str2array($onelineSQL);
    foreach ($ary as $key => $sql) {
        if ($sql === 'commit;' || $sql === '') {
            // PDOで経由で実行している為,commitが不要。
        } else {
            echo $sql . "\n";
            // SELECT文飲み
            if (preg_match('/select/i', $sql, $match)) {
                // $result = $AsazukeSiteScanDB->query($sql);
                // カラム名で結果取得
                $result = $AsazukeSiteScanDB->query($sql, \PDO::FETCH_ASSOC);
                echo "Result -> " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
                // foreach ($result as $key => $val) {
                // echo "Result -> ".json_encode($val, JSON_UNESCAPED_UNICODE). "\n";
                // }
            } else {
                echo "SELECT文以外は実行出来ません。\n";
            }
        }
    }
}

/**
 * サイト検証を行ったデータをCSV出力
 */
function scraping()
{
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
    echo "Finished!! (scraping)";
}

function siteValidationCsv()
{
    
    // php index.php site-validation-csv
    // サイト検証を行ったデータをCSV出力
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $result = $AsazukeSiteScanDB->selectAsazuke("1=1 ORDER BY filePath");
    
    $Asazuke = new Asazuke\Asazuke();
    $Asazuke->createPx2CSV($result);
}

function siteValidationResult()
{
    // php index.php site-validation-result
    // サイト検証を行ったデータを確認
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $result = $AsazukeSiteScanDB->selectAsazuke();
    {
        $jobTitle = 'LintResult';
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
}

/**
 *
 * @deprecated
 *
 */
function siteValidationCsvOrigin()
{
    // php index.php site-validation-csv
    // サイト検証を行ったデータをCSV出力
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $result = $AsazukeSiteScanDB->selectAsazuke();
    
    AsazukeUtil::createCSV($result);
}

function _csv()
{
    if (count($argv) >= 3) {
        // php index.php csv <depth>
        csv($argv[2]);
    } else {
        // php index.php show
        csv();
    }
}

function _show()
{
    if (count($argv) >= 3) {
        // php index.php show <depth>
        show($argv[2]);
    } else {
        // php index.php show
        show();
    }
}

function _schema()
{
    if (count($argv) >= 3) {
        // php index.php schema <table_name>
        schema($argv[2]);
    } else {
        // php index.php schema
        schema();
    }
}

/**
 * サイトスキャン（新規）
 */
function siteScan0()
{
    // データベースバックアップ
    date_default_timezone_set(Asazuke\AsazukeConf::$timezone);
    $db_file = Asazuke\AsazukeConf::getDbFile();
    $dt = date("md_His");
    rename($db_file, $db_file . ".${dt}");
    
    siteScan();
}

/**
 * サイトスキャン（再開）
 */
function siteScan()
{
    $AsazukeSiteScan = new Asazuke\AsazukeSiteScan();
    $AsazukeSiteScan->exec();
    echo "Finished!! (site-scan)";
}

/**
 * DBクエリー
 */
function query()
{
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $a = $AsazukeSiteScanDB->tables();
    var_dump($a);
}

function _tables()
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
 * select mac(id) from AsazukeSS; と同じ
 */
function countSiteScan()
{
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $a = $AsazukeSiteScanDB->select('1=1 ORDER BY id ASC');
    echo end($a)['id'];
}
function selectSiteScanById($id)
{
    $AsazukeSiteScanDB = new Asazuke\AsazukeDB();
    $a = $AsazukeSiteScanDB->select('id='.$id);
    echo (json_encode($a, JSON_UNESCAPED_UNICODE));
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

function defaultAction()
{
    $D = dirname(__FILE__);
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
    
    $php_src = file_get_contents($D . '/index.php');
    preg_match_all('/\s+if \(\$argv\[1\] === \'(.*)\'/', $php_src, $matches);
    echo "\n\n" . "[index.phpで使えるオプション]" . "\n";
    foreach ($matches[1] as $val) {
        echo "  $ index.php " . $val . "\n";
    }
    
    
    
    echo <<< EOL

'Asazuke'で使える、'composer'の詳しい説明は下記のリンクをご参照して下さい。
```
https://github.com/pickles2/asazuke#その他コマンド
```


EOL;
}
