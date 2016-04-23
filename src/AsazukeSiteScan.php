<?php
/**
 * サーバー上のリンクを辿ってファイルリストを作成
 */
namespace Mshiba\Px2lib\Asazuke;

//$D = dirname(__FILE__);
//require_once ($D . '/libs/phpQuery-onefile.php');

class AsazukeSiteScan
{
    private $console;

    /**
     * コンストラクタ
     * 初期化・変数定義
     * 1.
     * Create databases
     * 2. Open connections
     */
    public function __construct()
    {
      $this->console = new AsazukeUtilConsole();
    }

    /**
     * コンストラクタ
     * 初期化・変数定義
     * 1.
     * Create databases
     * 2. Open connections
     */
    public function exec()
    {
        $cID = 1;
        $path = AsazukeConf::$startPath;
        echo $path.PHP_EOL;
        

        $AsazukeSiteScanDB = new AsazukeDB();
        $aryData = array();
        $key = array();
        
        $mst = AsazukeConf::$url;
        $_url = AsazukeUtil::getResolvePath($path, $mst, $path);
        $data = AsazukeUtil::http_file_get_contents($_url, $response);
        var_dump($response);
        var_dump($response['reponse_code']);
        if ($response['reponse_code'] !== 200) {

        //extract($this->checkStatusCode($_url), EXTR_OVERWRITE);
          AsazukeUtil::logV('', '2XX以外は処理を中断する。');
          echo "開始URL:".$_url."が不正です。"."\n";
          return $statusCode;
        }else{
          $key['fullPath'] = AsazukeConf::$startPath;
          $key['checkCount'] = 0;
          $key['status'] = $response[0]; //"HTTP/1.1 200 OK"になるはず
          $key['statusCode'] = $response['reponse_code'];
          $aryData[] = $key;

          $lastInsertId = $AsazukeSiteScanDB->insert($aryData);

          if($lastInsertId > 0){
            // insertに成功したデータ
            $aryData[0]['id'] = $lastInsertId;
            echo "Result -> ".json_encode($aryData[0], JSON_UNESCAPED_UNICODE).PHP_EOL;
          }
          
          $this->oneFileExec($path);
          if(AsazukeConf::$ctrlCd){
            echo "\033[K" . "Finished -> " . $path . PHP_EOL; // ESC[K カーソル位置から行末までをクリア
          }else{
            echo "Finished -> " . $path . PHP_EOL; // ESC[K カーソル位置から行末までをクリア
          }
        }
        
        while (true) {

            $time_start = microtime(true) * 1000;

            // ここに実行処理(開始）
            {
                $AsazukeSiteScanDB = new AsazukeDB();
                $AsazukeSiteScanDB->updateChecked($cID); // 開始ID
                $result = $AsazukeSiteScanDB->select('checkCount=0 limit 1');

                // 進捗表示
                $progress = $AsazukeSiteScanDB->getSiteScanProgress();
                $this->console->out (PHP_EOL. $progress);
                
                if (! $result || count($result) == 0) {
                    break;
                } else {
                    AsazukeUtil::logV("NEXT", print_r($result, true));
                    $path = $result[0]['fullPath'];
                    $this->oneFileExec($path); // ここで実行
                    $cID = $result[0]['id']; // パスの先頭に"/"
                    if(AsazukeConf::$ctrlCd){
                      echo "\033[K" . "Finished -> " . $path . PHP_EOL; // ESC[K カーソル位置から行末までをクリア
                    }else{
                      echo "Finished -> " . $path . PHP_EOL; // ESC[K カーソル位置から行末までをクリア
                    }
                }
                $AsazukeSiteScanDB = null;
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
    }

    /**
     * 待ち処理 for Test
     */
    public function wait($callbackFunc, $parameter)
    {
        $time_start = microtime(true) * 1000;

        // ここに実行処理(開始）
        {
            call_user_func($callbackFunc, $parameter);
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

        // テスト用
        $exec_end = microtime(true) * 1000;
        $time = floor($exec_end - $time_start) * 1000;
        return $time;
    }

    /**
     * ステータスコードのチェックしつつ有効なURLとHTMLを返す。
     * @param $url
     * @return Mixed
     */
    public function checkStatusCode($url){
      $_html = '';
      $_statusCode = AsazukeMessage::$CD_XXX;
      $_retryCount = AsazukeConf::$retryCount;
      $_url = $url;
      while($_retryCount >= 0){
        $_retryCount--;
        $_html = AsazukeUtil::http_file_get_contents($_url, $r0);
        if (preg_match('/2\d{2}/', $r0['reponse_code'])) {
          // レスポンスコード2XX系の場合
          AsazukeUtil::logV(AsazukeMessage::$MSG_STATUS_CD.":".$r0['reponse_code'] , print_r($_url, true));
          $_statusCode = AsazukeMessage::$CD_2XX;
          break;
        }
        if (preg_match('/3\d{2}/', $r0['reponse_code'])) {
          // レスポンスコード3XX系の場合。Locationヘッダーを処理する。
          AsazukeUtil::logV(AsazukeMessage::$MSG_STATUS_CD.":".$r0['reponse_code'] , print_r($_url, true));
          foreach ($r0 as $idx => $header) {
            if(preg_match('/^Location\:/i', $header, $matches)){
              $_url = str_replace($matches, '', $header);
              break;
            }
          }
          $_statusCode = AsazukeMessage::$CD_3XX;
          continue;
        }
        if (preg_match('/4\d{2}/', $r0['reponse_code'])) {
          // レスポンスコード4XX系の場合
          AsazukeUtil::logV(AsazukeMessage::$MSG_STATUS_CD.":".$r0['reponse_code'] , print_r($_url, true));
          $_statusCode = AsazukeMessage::$CD_4XX;
          break;
        }
        if (preg_match('/5\d{2}/', $r0['reponse_code'])) {
          // レスポンスコード5XX系の場合
          AsazukeUtil::logV(AsazukeMessage::$MSG_STATUS_CD.":".$r0['reponse_code'] , print_r($_url, true));
          $_statusCode = AsazukeMessage::$CD_5XX;
          break;
        }
        if (parse_url($_url)['host'] !== parse_url(AsazukeConf::$url, PHP_URL_HOST)) {
          $_statusCode = AsazukeMessage::$CD_OTHER;
          break;
        }
      }
      return array('statusCode' => $_statusCode, 'html' => $_html, 'url' => $_url);
    }

    /**
     * href属性を取得
     * ※ <link href="*"> も対象になります。
     */
    public function getHref($html){
      // 2 phpQueryのドキュメントオブジェクトを生成
      
      // $doc = \phpQuery::newDocument($html);
      $doc = \phpQuery::newDocumentHTML($html);
      
      $aryA = array();
      // foreach ($doc["a"] as $a) {
      //     $link = pq($a)->attr('href');
      //     $aryA[] = $link;
      // }
      // foreach ($doc["area"] as $a) {
      //     $link = pq($a)->attr('href');
      //     $aryA[] = $link;
      // }
      foreach ($doc["*"] as $a) {
        $link = pq($a)->attr('href');
         // 空白削除
        $link = preg_replace('/ /', '', $link);
        // パラメータ削除
        if(preg_match('/\?/', $link) == 1){
          $link = explode('?', $link)[0];
        }

        if(!AsazukeConf::$isInsistently){
          $aryA[] = $link;
        }else{
          // ディレクトリ分割
          if (preg_match('/^https?/i', $link) == 1) {
            $aryA[] = $link;
          } else {
            $aryLink2 = $aryLink = explode('/', $link);
            foreach ($aryLink as $Link) {
              $aryA[] = implode('/', $aryLink2);
              array_pop($aryLink2);
            }
          }
        }
      }
      
      $filterAryA = array_filter($aryA, "strlen"); // 空配列を削除
      $sortAryA = array_unique($filterAryA, SORT_STRING); // 重複削除
      asort($sortAryA, SORT_STRING); // ソート
      // echo "---------------";
      // var_dump($sortAryA);
      return $sortAryA;
    }

    /**
     * 相対パスから、ルート相対パスを取得
     */
    public function getRootRelativeURLs($url, $v){
      // relative URLs(相対パス)
      extract(AsazukeUtil::ext_parse_url($url), EXTR_OVERWRITE);
      $parsed_url = parse_url($url);
      $newPath = '';
      if (AsazukeUtil::array_last(str_split($path)) === '/') {
          // echo "'/'で終わっている場合";
          $newPath = $path . $v;
      } else {
          // echo "'/'で終わっていない場合";
          // 拡張子判定
          if(AsazukeUtil::isFile($path)){
            $newPath = $path . $v;
          }else{
            $newPath = $path . '/' . $v;
          }
      }

      $newURL = AsazukeUtil::createUri($url, $newPath);
      $rootRelativeURLs = parse_url($newURL, PHP_URL_PATH);
      // echo $rootRelativeURLs.PHP_EOL;
      return $rootRelativeURLs;
    }

    /**
     * パス中の"."を解決する
     * $v = ルート相対パス
     */
    public function pathClean($v){
      $v_org = $v;
      $newRootRelativeURLs = AsazukeUtil::asRealPath($v);
      $v = $newRootRelativeURLs;
      // パス途中に'./'を含む場合は削除
      $v = preg_replace('/\.\//', '', $v);
      // 記述ミスなどにより//になっている？
      $v = preg_replace('/\/\//', '/', $v);
      AsazukeUtil::logV("pathClean", $v_org . ' -> ' . $v);
      return $v;
    }
    
    /**
     * メインロジック
     */
    public function oneFileExec($path, &$unitTestResult = array())
    {
        AsazukeUtil::logV('oneFileExec', $path);
        $resultStatus = false;
        $skipLinks = array();
        {
            // 例外処理
            if (preg_match('/^javascript/i', $path)) {
                // echo "javascriptで始まるURL";
                return AsazukeMessage::$CD_JS;
            }
            if (preg_match('/^mailto/i', $path)) {
              // echo "mailtoで始まるURL";
                return AsazukeMessage::$CD_MAILTO;
            }
            if (! preg_match('/^\//', $path)) {
              // echo "先頭に"/"が含まれていない場合";
                $path = '/' . $path;
            }
        }

        $mst = AsazukeConf::$url;
        $relative = $path;

        $_url = AsazukeUtil::getResolvePath($path, $mst, $relative);
        // $_url = preg_replace('/^http:/', 'https:', $_url);
        //echo "${_url}\n";

        extract($this->checkStatusCode($_url), EXTR_OVERWRITE);
        // echo "-------------------URL:". $_url;
        // echo $html;
        //echo '$statusCode:'.$statusCode."\n";
        if($statusCode !== AsazukeMessage::$CD_2XX){
          AsazukeUtil::logV('', '2XX以外は処理を中断する。');
          return $statusCode;
        }
        AsazukeUtil::logV("URL(on Database)", $_url);
        echo ("URL(on Database)" .$_url.PHP_EOL);
        try {
          // \phpQuery::newDocument($html); でfatal errorがでる場合があるので事前チェックを実施
          echo 'loadHTML:::.'."\n";
          // 文字コード変換(utf8化)
          // $ary = $this->text2utf8($html);
          // $html = $ary[0];
          // $cp =  $ary[1];
          
          // $bool = \DOMDocument::loadHTML($html);
          // if(!$bool){
          //   echo "Skip -> ".$url. ' message:htmlとして処理出来ませんでした。';
          //   //return true;
          // }
            
            $sortAryA = $this->getHref($html);
            // var_dump($sortAryA);
            $AsazukeSiteScanDB = new AsazukeDB();

            foreach ($sortAryA as $k => $v) {
                AsazukeUtil::logV("URL(on HTML)", $v);
                if (preg_match('/^https?/i', $v) == 1) {
                    AsazukeUtil::logV('', '絶対パス http(s)から始まる');
                    $mixed = parse_url($v);
                    if ($mixed['host'] === parse_url(AsazukeConf::$url, PHP_URL_HOST)) {
                        $v = $mixed['path'];
                        AsazukeUtil::http_file_get_contents(AsazukeConf::$url.$v , $response);
                    } else {
                      // 外部リンク
                      $skipLinks[] = array(AsazukeMessage::$SKIP_OTHER, $v);
                      continue;
                    }
                } else {
                    if (! AsazukeUtil::asazukefilter($v, $path)) {
                        // 処理しないリンク
                        $skipLinks[] = array(AsazukeMessage::$SKIP_Filter, $v);
                        continue;
                    }
                    if (preg_match('/^\/\//', $v)) {
                        // network-path reference(ネットワークパス参照) "//"から始まる場合
                        $newURL = 'http:' . $v;
                        AsazukeUtil::http_file_get_contents($newURL, $r1);
                        if ($r1['reponse_code'] !== AsazukeMessage::$CD_2XX) {
                            // 2XX以外の場合
                            // 通常のパスとして再検証する。
                            AsazukeUtil::http_file_get_contents(AsazukeConf::$url . $v, $r2);
                            if ($r2['reponse_code'] !== AsazukeMessage::$CD_2XX) {
                                AsazukeUtil::logV($r2['reponse_code'], print_r(AsazukeConf::$url . $v, true));
                                $skipLinks[] = array(AsazukeMessage::$SKIP_NOT_FOUND, $v);
                                continue;
                            } else {
                              // "//dir/file" のような記述ミスの可能性があるので、"/dir/file"に修正する。
                              $v = preg_replace('/^\/\//', '/', $v);
                            }
                        } else {
                            $v = parse_url($newURL, PHP_URL_PATH);
                        }
                    } elseif (preg_match('/^\//', $v)) {
                        AsazukeUtil::logV('', 'root-relative URLs (ルート相対パス) "/"(ディレクトリ名|ファイル名)');
                        $v = parse_url(AsazukeConf::$url . $v, PHP_URL_PATH);
                    } else {
                        AsazukeUtil::logV('', 'relative URLs(相対パス) "./" or "../" or "(ディレクトリ名|ファイル名)" から始まっている場合');
                        $rootRelativePath = parse_url($_url, PHP_URL_PATH);
                        $rootRelativePath_dir = AsazukeUtil::ext_dirname($rootRelativePath);
                        //echo '$rootRelativePath:'.$rootRelativePath."\n";
                        //echo '$rootRelativePath_dir:'.$rootRelativePath."\n";
                        //echo '$v:'.$v."\n";
                        if($rootRelativePath === '/' && $rootRelativePath_dir === '/'){
                           // http://getbootstrap.com の <a href="../javascript/"> のような描き方への対応
                           $v = parse_url(AsazukeConf::$url.$v, PHP_URL_PATH);
                        }else{
                           $v = parse_url(AsazukeConf::$url .$this->getRootRelativeURLs(AsazukeConf::$url.$rootRelativePath_dir, $v), PHP_URL_PATH);
                        }
                    }

                    AsazukeUtil::logV('', '解決したルート相対パスを使って有効なURLか調べる。');
                    echo '$v:'.$v."\n";

                    AsazukeUtil::http_file_get_contents(AsazukeConf::$url.$v , $response);
                    //echo $response['reponse_code']."\n";
                    if (preg_match('/2\d{2}/', $response['reponse_code'])
                    || preg_match('/3\d{2}/', $response['reponse_code'])){
                      // 200系 or 300系
                      $v = $this->pathClean($v);;
                    } else {
                      AsazukeUtil::logV('', '相対パスとしても処理してみる。');
                      extract(AsazukeUtil::ext_parse_url($url), EXTR_OVERWRITE);
                      if(AsazukeUtil::isFile($path)){
                        $concatURL = $url. AsazukeUtil::ext_dirname($path).$v;
                      }else{
                        $concatURL = $url.$path.$v;
                      }
                      //echo "[3]".$concatURL.PHP_EOL.PHP_EOL;

                      AsazukeUtil::http_file_get_contents($concatURL, $response);
                      if ($response['reponse_code'] !== AsazukeMessage::$CD_2XX) {
                        AsazukeUtil::logV($response['reponse_code'], print_r($concatURL, true));
                        $skipLinks[] = array(AsazukeMessage::$SKIP_NOT_FOUND, $v);
                        continue;
                      }else{
                        $urlPath = parse_url($concatURL, PHP_URL_PATH);
                        $v = $this->pathClean($urlPath);
                      }
                    }

                    AsazukeUtil::logV('', '絶対パスなどを処理');
                    $paths = array_values(array_filter(explode('/', $v)));
                }
                AsazukeUtil::logV('', 'AsazukeConf::$startDir配下のディレクトリか判定する。');
                $urlPath = parse_url($v, PHP_URL_PATH);
                $starDir = AsazukeUtil::ext_dirname(AsazukeConf::$startPath);
                //echo $urlPath. "\n";
                //echo $starDir. "\n";
                if(preg_match("/^" . preg_quote($starDir, "/") . "/", $urlPath) == 1){
                    AsazukeUtil::logV('[Ok]', $urlPath);
                } else {
                    AsazukeUtil::logV('[Skip]:', $urlPath. " is `". $starDir. "` matches.");
                    $skipLinks[] = array(AsazukeMessage::$SKIP_CD01, $urlPath. " is `". $starDir. "` matches.");
                    continue;
                }

                $aryData = array();
                $key = array();
                $key['fullPath'] = $v;
                
                $key['checkCount'] = 0;
                $key['status'] = $response[0];
                $key['statusCode'] = $response['reponse_code'];
                $unitTestResult['validLinks'][] = $key;
                $aryData[] = $key;

                $lastInsertId = $AsazukeSiteScanDB->insert($aryData);

                if($lastInsertId > 0){
                  // insertに成功したデータ
                  $aryData[0]['id'] = $lastInsertId;
                  echo "Result -> ".json_encode($aryData[0], JSON_UNESCAPED_UNICODE).PHP_EOL;
                }

            }
        } catch (Exception $e) {
            AsazukeUtil::logE("DOM", print_r($e, true));
        }
        $unitTestResult['skipLinks'] = $skipLinks;

        $AsazukeSiteScanDB = null;
        $resultStatus = true;
        return $resultStatus;
    }
}
?>
