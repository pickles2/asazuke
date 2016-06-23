<?php
/**
 *サイト解析、エラーチェック、コンテンツ取得、　サイトマップCSV作成用
 */
namespace Mshiba\Px2lib\Asazuke;

//$D = dirname(__FILE__);
//require_once ($D . '/libs/phpQuery-onefile.php');

class Asazuke
{
    private $console;

    public $csvColmns = array(
        'massage',
        'filepath'
    );

    /**
     * コンストラクタ
     * 初期化・変数定義
     */
    public function __construct()
    {

        // ディレクトリ作成
        $datDir = dirname(AsazukeConf::getDat());
        if (! file_exists($datDir)) {
            if (! mkdir($datDir, 0777, true)) {
                die('Failed to create folders...');
            }
        }
        $htmlDir = dirname(AsazukeConf::getHtml());
        if (! file_exists($htmlDir)) {
            if (! mkdir($htmlDir, 0777, true)) {
                die('Failed to create folders...');
            }
        }
        $cssDir = dirname(AsazukeConf::getCss());
        if (! file_exists($cssDir)) {
            if (! mkdir($cssDir, 0777, true)) {
                die('Failed to create folders...');
            }
        }
        $scrDir = dirname(AsazukeConf::getScrapingHtml());
        if (! file_exists($scrDir)) {
            if (! mkdir($scrDir, 0777, true)) {
                die('Failed to create folders...');
            }
        }
    }

    /**
     * CSVから実行
     *
     * @param unknown $aryCsv
     */
    public function execFromCSV($aryCsv)
    {
        $aryCsv = $this->loadCSV();
        $this->kick($aryCsv);
    }

    /**
     * データを元に実行
     */
    public function exec($aryCsv)
    {
        // 何も処理しないでファイルのダウンロードを行う
        $this->console = new AsazukeUtilConsole();
        $AsazukeDB = new AsazukeDB();

        $ci = 0;
        foreach ($aryCsv as $recoad) {


            $time_start = microtime(true) * 1000;

            // ここに実行処理(開始）
            {
                $message = $recoad[$this->csvColmns[0]];
                $path = $recoad[$this->csvColmns[1]];

                if (!function_exists('tidy_parse_string'))
                {
                    AsazukeMessage::error_tidy();
                }

                // ソースの取得
                $url = AsazukeConf::$url . $path;
                $html = AsazukeUtil::http_file_get_contents($url, $response, false);

                //$html = mb_convert_encoding($html, 'utf8', 'sjis-win');
                mb_language("Japanese");
                $cp = mb_detect_encoding($html, "ASCII,JIS,UTF-8,CP51932,SJIS-win", true);
                echo $cp;
                $html = mb_convert_encoding($html, "UTF-8", $cp); 

                $aryAsazuke = array();
                $key = array();
                $key['filePath'] = $path;
                $aryAsazuke[] = $key;
                
                $lastInsertId = $AsazukeDB->insertAsazuke($aryAsazuke);
                $htmlPath = AsazukeUtil::getDatPath($lastInsertId, AsazukeConf::getHtml());
                
                $AsazukeUtilFile = new AsazukeUtilFile($htmlPath, true);
                echo $htmlPath."\n";
                $AsazukeUtilFile->out($html);
            }
            // ここに実行処理(終了）
            
            $Sec_1 = 100 * 10000;
            $wait = $Sec_1 / AsazukeConf::$execPerSecond;
            $time_end = microtime(true) * 1000;
            $time = floor($time_end - $time_start) * 1000;
            if ($wait > $time) {
                $t = ceil($wait - floor($time));
                usleep($t);
            }
        }
        $this->console->close();
        echo "\n";

        gc_collect_cycles();
    }

    public function file_append($fil, $contents)
    {
        $current = file_get_contents($file);
        $current .= $contents;
        // 結果をファイルに書き出します
        file_put_contents($file, $current);
    }

    /**
     * CSVの読み込み
     *
     * @return $csv 行毎に分割された配列
     *         <pre>
     *         array(1) {
     *         [0]=>array(2) {
     *         ["massage"]=>string(3) "top",
     *         ["filepath"]=>string(1) "/"
     *         }
     *         }
     *         </pre>
     */
    public function loadCSV()
    {
        $path = AsazukeConf::getCsv();
        if (false === file_exists($path)) {
            die("error occured while trying to process file_get_contents. file path :{$path} is not valid. ");
        }
        $data = file_get_contents($path);
        $data = mb_convert_encoding($data, 'UTF-8', 'CP932');

        $temp = tmpfile();
        $csv = array();

        fwrite($temp, $data);
        rewind($temp);

        while (($data = fgetcsv($temp, 0, ",")) !== FALSE) {
            $a = array();
            $a[$this->csvColmns[0]] = $data[0];
            $a[$this->csvColmns[1]] = $data[1];
            $csv[] = $a;
            // $data = implode(",", $data);
            // // htmlタグが文字化けするのでHTML エンティティに変換
            // // 表示する時にHTML エンティティのデコードする
            // $csv[] = htmlentities($data);
        }
        fclose($temp);
        return $csv;
    }

    /**
    * @param string $html 解析するHTML
    * @return array preg_match_allの結果
    */
    public function grepHref($html) {
        $ptn='/<a[^>]href\s?=\s?[\"\']([^\"\']+)[\"\'][^>]*>/i';
        preg_match_all($ptn, $html, $m);
        return $m;
    }

  /**
    * @param string $html 解析するHTML
    * @return array preg_match_allの結果
    */
    public function grepSrc($html) {
        $ptn='/<a[^>]src\s?=\s?[\"\']([^\"\']+)[\"\'][^>]*>/i';
        preg_match_all($ptn, $html, $m);
        return $m;
    }

    /**
     * cssとsrcのパス解決
     */
    public function pathResolve($aryPaths, $aryIndex, $relativePath)
    {
        $pathP = $aryPaths[$aryIndex];

        $mst = AsazukeConf::$url;

        // ファイルを1行ずつ処理
        $newfile = "";
        $file = fopen($pathP, "r");
        
        if($file){
            while ($line = fgets($file)) {
                
                $aryHref = $this->grepHref($line);
                $arySrc = $this->grepSrc($line);
                $sortAryH = $aryHref[1];
                $sortAryS = $arySrc[1];
                
                foreach ($sortAryH as $path) {
                    $path2 = AsazukeUtil::getResolvePath($path, $mst, $relativePath);
                    $line = str_replace('href="' . $path . '"', 'href="' . $path2 . '"', $line);
                }

                // <img src=""> の処理
                foreach ($sortAryS as $path) {
                    $path2 = AsazukeUtil::getResolvePath($path, $mst, $relativePath);
                    $line = str_replace('src="' . $path . '"', 'src="' . $path2 . '"', $line);
                }
                $newfile .= $line;
            }
        }
        fclose($file);
        return $newfile;
    }

    /**
     *
     * @param unknown $htmlPath
     */
    public function concatcss($htmlPath)
    {
        $AsazukeUtilFile = new AsazukeUtilFile("1.css");
        if (false) {
            // STEP1
            // サーバー上のlinkのcssファイルを結合
            echo "\n" . $htmlPath;

            $html = file_get_contents($htmlPath);

            $tidy = tidy_parse_string($html, array(), AsazukeConf::$tidyEncoding);
            $tidy->cleanRepair();

            $pattern = '/Error:/';
            $matchesErr = preg_grep($pattern, AsazukeUtil::str2array($tidy->errorBuffer));

            $pattern = '/Warning:/';
            $matchesWar = preg_grep($pattern, AsazukeUtil::str2array($tidy->errorBuffer));

           // $doc = \phpQuery::newDocument($html);
            $doc = \phpQuery::newDocumentHTML($html);

            $aryHref = array();
            foreach ($doc["link"] as $elem) {
                $aryHref[] = pq($elem)->attr('href');
            }

            // cssファイル抽出
            $pattern = '/.css$/';
            $matchesWar = preg_grep($pattern, $aryHref);

            foreach ($matchesWar as $cssUri) {
                $AsazukeUtilFile->out(file_get_contents($cssUri));
            }
        }
        // if(true){
        // STEP2
        // @import 解決
        $cssFile = $AsazukeUtilFile->getFileName();
        $pattern = '/import/';
        $aryAtImport = preg_grep($pattern, AsazukeUtil::str2array(file_get_contents($cssFile)));

        var_dump($aryAtImport);

        // }
        $matchesWar = null;
        return $matchesWar;
    }

    /**
     * Pickles2用のCSVサイトマップを作成
     * TODO
     *
     * @param unknown $result
     */
    public function createPx2CSV($result)
    {
        $dt = date("md_His");
        $csvName = AsazukeConf::$projectName."-output${dt}.csv";
        // $stream = fopen($csvName, 'w');
        $AsazukeUtilFile = new AsazukeUtilFile($csvName, true);
        $expfile = realpath($AsazukeUtilFile->getFileName());

        $aryCsvColNames = array_keys(AsazukeConf::$csv_cols);
        // print_r($aryCsvColNames); // csv列名

        $encoding = AsazukeConf::$csv_format["encoding"];
        $linefeed = AsazukeConf::$csv_format["linefeed"];
        // 列
        // fwrite($stream, mb_convert_encoding(implode(',', AsazukeUtil::arrayQuote($aryCsvColNames)), 'SJIS-win', 'UTF-8') . "\r\n");
        $AsazukeUtilFile->out(mb_convert_encoding(implode(',', AsazukeUtil::arrayQuote($aryCsvColNames)), $encoding, 'UTF-8') . $linefeed, true);

        // $aryColName = AsazukeUtil::getColumnName($result);
        // print_r($aryColName); // csv列名
        // $mst = AsazukeConf::$url;
        $pg = array(
            '|',
            '/',
            '-',
            '\\'
        );
        $p = 0;

        foreach ($result as $data) {
            $path = $data['filePath'];
            $id = $data['id'];

            if(AsazukeConf::$ctrlCd){
              echo "\r" . $pg[++ $p % count($pg)];
            }else{
              echo (++$p)."@id[${id}]:".$path."処理中"."\n";
            }


            if (! AsazukeUtil::asazukefilter($path)) {
                // 処理しないリンク
                continue;
            }

            $cssWorksFile = AsazukeUtil::getDatPath($id, AsazukeConf::getHtml());

            // echo $path . "\n";
            // echo $cssWorksFile . "\n";
            {
                $html = file_get_contents($cssWorksFile);
                
                // "<meta http-equiv="を削除
                {
                    // \g シーケンスで負の数値を使うと、 それは相対参照
                    // \g{-2}は相対指定で2つ前の'([\"\'])'と一致したものを使う
                    $ptn='/<meta(.*?)[^>]http-equiv\s*=\s*([\"\'])(.*?)\g{-2}[^>]*>/i';
                    preg_match_all($ptn, $html, $m);
                    // 複数の <meta http-equiv="?"> を空文字に置換
                    $html = str_replace($m[0], '', $html);
                }
                
                //$tidy = tidy_parse_string($html, array(), AsazukeConf::$tidyEncoding);
                //$tidy->cleanRepair();

     //           $html = $ary[0];
     //           echo $html;
                //$doc = \phpQuery::newDocument($html);
                $doc = \phpQuery::newDocumentHTML($html);
                //var_dump($doc);

                // キーを指定して、配列を値で埋める
                $csvRowData = array_fill_keys($aryCsvColNames, '');

                // 決め打ちの動作
                {
                    // path
                    $csvRowData['* path'] = $path;
                }
                {
                    // title
                    $selecter = AsazukeConf::$csv_cols['* title'];
                    $csvRowData['* title'] = $doc[$selecter]->text();
                }
                {
                    // title_breadcrumb
                    $selecter = AsazukeConf::$csv_cols['* title_breadcrumb'];
                    $csvRowData['* title_breadcrumb'] = pq($doc[$selecter])->text();
                }
                {
                    // logical_path
                    $selecter = AsazukeConf::$csv_cols['* logical_path'];
                    $aryData = array();
                    foreach ($doc[$selecter] as $elem) {
                        $url = pq($elem)->attr('href');
                        // ドメインは含まない
                        $aryData[] = parse_url($url, PHP_URL_PATH);
                    }
                    //var_dump('logical_path', $selecter, $aryData);

                    // トップページは含まない
                    $mixed = array_search('/', $aryData);
                    if ($mixed !== FALSE) {
                        unset($aryData[$mixed]);
                    }
                    // 自分自身は含まない
                    $mixed = array_search($path, $aryData);
                    if ($mixed !== FALSE) {
                        unset($aryData[$mixed]);
                    }

                    $csvRowData['* logical_path'] = implode('>', $aryData);
                }
                {
                    // meta keywords
                    $selecter = AsazukeConf::$csv_cols['* keywords'];
                    $csvRowData['* keywords'] = pq($doc[$selecter])->attr('content');
                }
                {
                    // meta description
                    $selecter = AsazukeConf::$csv_cols['* description'];
                    $csvRowData['* description'] = pq($doc[$selecter])->attr('content');
                }
                {
                    if (AsazukeConf::$isDebuggable) {
                        // デバッグ用
                        $csvRowData['$cssWorksFile'] = $cssWorksFile;
                    }
                }

                // 上記以外の動作
                $otherCSVData = array_keys(array_filter($csvRowData, function ($k) {
                    return $k === '';
                })); // ''のモノのみ
                foreach ($otherCSVData as $csvKey) {
                    $cssSelector = AsazukeConf::$csv_cols[$csvKey];

                    if (preg_match('#{(.*)?}#', $cssSelector, $matches)) {
                        // $csv_colsに固定値の設定
                        //echo '$csv_colsに固定値の設定:'. $matches[1]." ". $csvKey ."\n";
                        $csvRowData[$csvKey] = $matches[1];
                    } elseif (strlen($cssSelector) > 0) {
                        // 上記以外のCSSセレクタを処理

                        // A用
                        if($csvKey === '* sitecatalyst'){
                            // $csvRowData[$csvKey] = pq($doc[$cssSelector])->htmlOuter();
                            $csvRowData[$csvKey] = AsazukeUtil::stripReturn(pq($doc[$cssSelector])->htmlOuter());
                        }else if($csvKey === '* apple-touch-icon'){
                            // $csvRowData[$csvKey] = pq($doc[$cssSelector])->htmlOuter();
                            $csvRowData[$csvKey] = pq($doc[$cssSelector])->attr('href');
                        }
                        
                        // T用
                        //echo '$csvKey:'.$csvKey."\n";
                        //   if($csvKey === '* sitecatalyst1'){
                        //     // $csvRowData[$csvKey] = pq($doc[$cssSelector])->htmlOuter();
                        //     $csvRowData[$csvKey] = AsazukeUtil::stripReturn(pq($doc[$cssSelector])->htmlOuter());
                        //   }else if($csvKey === '* sitecatalyst2'){
                        //     $scripts = array();
                        //     foreach($doc[$cssSelector] AS $obj){
                        //       $scriptTag = pq($obj)->htmlOuter();
                        //       array_push($scripts, $scriptTag);
                        //     }
                        //     // var_dump($scripts);
                        //     // die();
                        //     // $csvRowData[$csvKey] = implode(',',preg_grep("/(SCoutput_bc)/s", $scripts));
                        //     $csvRowData[$csvKey] = AsazukeUtil::stripReturn(implode(',',preg_grep("/(SCoutput_bc)/s", $scripts)));
                        //   }
                        
                        else{
                            $csvRowData[$csvKey] = pq($doc[$cssSelector])->attr('content');
                        }
                      
                      //echo '上記以外のCSSセレクタを処理:'. $cssSelector .":". pq($doc[$cssSelector])->attr('content')."\n";
                    }
                }
                // var_dump($csvRowData);
                // fwrite($stream, mb_convert_encoding(implode(',', AsazukeUtil::arrayQuote($csvRowData)), 'SJIS-win', 'UTF-8') . "\r\n");
                $AsazukeUtilFile->out(mb_convert_encoding(implode(',', AsazukeUtil::arrayQuote($csvRowData)), $encoding, 'UTF-8') . $linefeed, true);
            }
        }
        echo 'Finished -> '. $expfile . "\n";
    }

    public function scrapingHTML($result)
    {
        $mst = AsazukeConf::$url;
        $tmpId = array();
        $key = key(AsazukeConf::$export_html);

        $pg = array(
            '>   ',
            '>>  ',
            '>>> ',
            '>>>>'
        );
        $p = 0;
        foreach ($result as $data) {
            echo "\r" . $pg[++ $p % count($pg)];

            $path = $data['filePath'];
            $id = $data['id'];
            $newDir = AsazukeUtil::getResolvePath($path, $mst, $path);
            if ($path === $newDir) {
                // TODO 一致しない場合はPickles2には不要なデータとみなす
                continue;
            } elseif (preg_match('#^\.#', $path)) {
                // "."から始まる場合も不要なデータとみなす
                continue;
            }
            if (! preg_match('#^/#', $path)) {
                // "/"から始まらない
                $path = "/" . $path;
            }
            $expHtdocs = AsazukeConf::getExpHtdocs();
            $expPath = $expHtdocs . $path;

            $newDir = '';
            $newFile = '';
            if (end(str_split($expPath)) === '/') {
                // echo "'/'で終わっている場合";
                $newDir = $expPath;
                $newFile = $expPath . 'index.html';
            } else {
                // echo "'/'で終わっていない場合、たぶんファイルで終わっている場合";
                $newDir = dirname($expPath) . '/';
                $newFile = $expPath;
            }

            // echo $newDir."\n";
            // echo $newFile."\n";

            if (! is_dir($newDir)) {
                if (! mkdir($newDir, 0777, true)) {
                    echo "Failed to create folders...'\n";
                }
            }

            //$cssWorksFile = AsazukeUtil::getDatPath($id, AsazukeConf::getCss());
            $cssWorksFile = AsazukeUtil::getDatPath($id, AsazukeConf::getHtml());

            // echo $path . "\n";
            // echo $cssWorksFile . "\n";
            {
                $html = file_get_contents($cssWorksFile);
                $tidy = tidy_parse_string($html, array(), AsazukeConf::$tidyEncoding);
                $tidy->cleanRepair();

                //$doc = \phpQuery::newDocument($html);
                $doc = \phpQuery::newDocumentHTML($html);

                $newPath = AsazukeUtil::getDatPath($id, AsazukeConf::getScrapingHtml());
                // $stream = fopen($newPath, 'w');
                $AsazukeUtilFile = new AsazukeUtilFile($newPath, true);

                // linkタグ
                $html = $doc['link']->htmlOuter();
                // fwrite($stream, $html . "\n\n\n\n");
                // phpタグでコメントアウト
                $AsazukeUtilFile->out('<?php' . "\n" . '/**' . "\n");
                // $AsazukeUtilFile->out('<?php' . "\n" . '$comment'." = <<<EOT");
                $AsazukeUtilFile->out($html, true);
                $AsazukeUtilFile->out("\n" . '*/' . "\n" . '?>');
                // $AsazukeUtilFile->out("\nEOT;" . "\n" . '? >');
                $AsazukeUtilFile->out("\n\n\n\n", true);

                // scriptタグ
                $html = $doc['script']->htmlOuter();
                // fwrite($stream, $html . "\n\n\n\n");
                // phpタグでコメントアウト
                $AsazukeUtilFile->out('<?php' . "\n" . '/**' . "\n");
                // $AsazukeUtilFile->out('<?php' . "\n" . '$comment'." = <<<EOT");
                $AsazukeUtilFile->out($html, true);
                $AsazukeUtilFile->out("\n" . '*/' . "\n" . '?>');
                // $AsazukeUtilFile->out("\nEOT;" . "\n" . '? >');
                $AsazukeUtilFile->out("\n\n\n\n", true);

                // 対象タグ
                foreach (AsazukeConf::$export_html as $export_tag) {
                    $AsazukeUtilFile->out('<!-- ' . $export_tag['name'] . ' -->');
                    $selecter = $export_tag['selector'];
                    $scope = $export_tag['scope'];
                    if ($scope === 'outerHTML') {
                        $html = $doc[$selecter]->htmlOuter();
                    } elseif ($scope === 'innerHTML') {
                        $html = $doc[$selecter]->html();
                    } else {
                        $html = $doc[$selecter]->html();
                    }
                    // fwrite($stream, $html);
                    //相対パス置換
                    {
                          $r_tidy = tidy_parse_string($html, array(), AsazukeConf::$tidyEncoding);
                          $r_tidy->cleanRepair();

                          //$r_doc = \phpQuery::newDocument($html);
                          $doc = \phpQuery::newDocumentHTML($html);
                          $r_aryHref = array();
                          //$arySrc = array();
                          foreach ($r_doc["*"] as $r_elem) {
                              $r_aryHref[] = pq($r_elem)->attr('href');
                              //$arySrc[] = pq($r_elem)->attr('src');
                          }
                          // urlの長いものから順に処理する。
                          $r_aryHref = array_values(array_filter($r_aryHref, "strlen"));
                          usort($r_aryHref, function($a, $b){
                            return ($a < $b) ? -1 : 1;
                          });
                          //var_dump($r_aryHref);
                          $m_mix = parse_url(AsazukeConf::$url);
                          foreach($r_aryHref AS $r_idx => $r_val){
                            $r_mix = parse_url($r_val);
                            //echo $r_mix['host'] . ' + ' . $r_mix['path']. "\n";
                            //echo $m_mix['host'] . "\n";
                            if($r_mix['host'] === $m_mix['host']){
                              $html = str_replace($r_mix['scheme'].'://'.$r_mix['host'].$r_mix['path'], $r_mix['path'], $html);
                            }
                          }
                          $AsazukeUtilFile->out($html . "\n\n\n\n", true);
                    }
                    
                }

                $tmpId[] = $id;

                // $expFIle = $expPath . 'index.html';
                // echo $newPath. "\n";
                // echo $expFIle. "\n";
                // echo "\n";
                $bool = copy($newPath, $newFile);
                if (! $bool) {
                    AsazukeUtil::logV("copy", "copyに失敗しました。 " . $newPath);
                } else {
                    AsazukeUtil::logV("copy", $newFile);

                    try {
                        // 画像などのリソースなどもダウンロードする
                        $html = file_get_contents($newFile);
                        //$doc = \phpQuery::newDocument($html);
                        $doc = \phpQuery::newDocumentHTML($html);
                        foreach ($doc["img"] as $img) {
                            $imgPath = pq($img)->attr('src');
                            $v = parse_url($imgPath);
                            $path = $v['path'];
                            if (preg_match('#^//#', $path)) {
                                $path = preg_replace('#^//#', '/', $path);
                            }
                            // var_dump($path); // 画像パス

                            $ch = curl_init($imgPath);
                            $savePath = $expHtdocs . $path;
                            $saveDir = dirname($savePath);
                            if (! file_exists($saveDir)) {
                                if (! mkdir($saveDir, 0777, true)) {
                                    die('Failed to create folders...');
                                }
                            }
                            // ファイル保存
                            // var_dump($savePath); // 画像パス
                            $fp = fopen($savePath, 'w');
                            curl_setopt($ch, CURLOPT_FILE, $fp);
                            curl_setopt($ch, CURLOPT_HEADER, FALSE);
                            $result = curl_exec($ch);
                            curl_close($ch);
                            fclose($fp);

                            // 元ファイルの文字置換
                            $buf = file_get_contents($newFile);
                            $buf = str_replace($imgPath, $path, $buf);
                            file_put_contents($newFile, $buf);
                        }
                    } catch (Exception $e) {
                        var_dump($e);
                    }
                }
            }
        }

        $mstDir = dirname(AsazukeConf::getScrapingHtml());
        $mstHTML = $mstDir . '/mst.html';
        // $stm = fopen($mstHTML, 'w');
        $AsazukeUtilFile = new AsazukeUtilFile($mstHTML, true);

        $s = '<div>' . "\n";
        foreach ($tmpId as $i => $id) {
            if ($i % 10 == 0) {
                $s .= '</div><div>' . "\n";
            }
            $s .= '<a target="_blank" href="file:///' . $mstDir . '/' . $id . '.html">' . $id . '</a>' . "\n";
        }
        $s .= '</div>' . "\n";

        $template = <<< EOF
<!DOCTYPE html>
<html>
<head>
<title></title>
<meta charset="utf-8">
<style>
body{text-align:center; margin:0;}
a {
display: inline-block;
width: 40px;
background-color: gray;
text-align: center;
color: #fff;
border: 1px solid #000;
padding: 3px;
margin-bottom: 3px;
}
</style>
</head>
<body>
${s}
</body>
</html>
EOF;

        $AsazukeUtilFile = new AsazukeUtilFile($mstHTML, true);
        // fwrite($stm, $template);
        $AsazukeUtilFile->out($template, true);

        if (is_dir($expHtdocs)) {
            $expHtdocs .= '/';
        }

        echo <<< EOL

[scraping html]
${mstHTML}

[sample_site]
${expHtdocs}


EOL;
    }

}
