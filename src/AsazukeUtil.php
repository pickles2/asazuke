<?php
namespace Mshiba\Px2lib\Asazuke;

use Symfony\Component\Finder\Finder;

class AsazukeUtil
{

    public static function logV($tag, $text)
    {
        $fileLog = new AsazukeUtilFile(AsazukeConf::getLogPath());
        $fileLog->out("[Verbose] $tag " . self::stripReturn($text));
        if(AsazukeConf::$isDebuggable){
          echo $text.PHP_EOL;
          //fwrite(STDOUT, $text.PHP_EOL);

        }
    }

    public static function logW($tag, $text)
    {
        $fileLog = new AsazukeUtilFile(AsazukeConf::getLogPath());
        $fileLog->out("[Warning] $tag " . self::stripReturn($text));
    }

    public static function logE($tag, $text)
    {
        echo "[Error] $tag $text \n";
        $fileLog = new AsazukeUtilFile(AsazukeConf::getLogPath());
        $fileLog->out("[Error] $tag " . self::stripReturn($text));
    }

    /**
     * "1st, 2nd, 3rd, 4th..."を返す
     *
     * @param unknown $i
     * @return string
     */
    public static function getCounting($i)
    {
        $s = $i . "th";
        if (! ($i >= 11 && $i <= 13)) {
            $arr1 = str_split(strval($i));
            if (end($arr1) == "1") {
                $s = $i . "st";
            } elseif (end($arr1) == "2") {
                $s = $i . "nd";
            } elseif (end($arr1) == "3") {
                $s = $i . "rd";
            }
        }
        return $s;
    }

    /**
     * parse_urlの拡張
     */
    public static function ext_parse_url($url){
      $parsed_url = parse_url($url);
      $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
      $host     = isset($parsed_url['host'])   ? $parsed_url['host'] : '';
      $port     = isset($parsed_url['port'])   ? ':' . $parsed_url['port'] : '';
      $path     = isset($parsed_url['path'])   ? $parsed_url['path'] : '';
      $url      = $scheme . $host. $port;
      return array('scheme' => $scheme,'host' => $host,'port' => $port,'path' => $path,'url' => $url);
    }

    /**
     * 改行削除
     *
     * @param unknown $text
     * @return unknown
     */
    public static function stripReturn($text, $replacement='')
    {
        $text = preg_replace(array(
            '/\r\n/',
            '/\r/',
            '/\n/'
        ), $replacement, $text);
        return $text;
    }

    public static function stripWiteSpace($text)
    {
        $text = preg_replace("/( |　)/", "", $text);
        return $text;
    }

    /**
     * datファイルのパス取得（#{}の文字を置換）
     *
     * @param unknown $ID
     * @return unknown
     */
    public static function getDatPath($ID, $filepath)
    {
        $path = preg_replace('/#\{(.*?)\}/', $ID, $filepath);
        return $path;
    }

    /**
     * テキストデータを改行("\n")で分割
     *
     * @param
     *            $text
     * @return $array 行毎に分割された配列
     */
    public static function str2array($text)
    {
        $array = explode("\n", $text); // とりあえず行に分割
        $array = array_map('trim', $array); // 各要素をtrim()にかける
        $array = array_filter($array, 'strlen'); // 文字数が0のやつを取り除く
        $array = array_values($array); // これはキーを連番に振りなおしてるだけ
        return $array;
    }

    /**
     * curlを使ってレスポンスヘッダーのみ取得
     */
    public static function get_headers_curl($url) 
    { 
        $ch = curl_init(); 

        curl_setopt($ch, CURLOPT_URL,            $url); 
        curl_setopt($ch, CURLOPT_HEADER,         true); 
        curl_setopt($ch, CURLOPT_NOBODY,         true); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_TIMEOUT,        15); 

        $r = curl_exec($ch); 
        $r = explode("\n", $r); 
        return $r; 
    }
    
    /**
     * URLがディレクトリかどうか判断
     * ※末尾の/を削除してアクセスすることで301が返るか否かを使って判断する。
     */
    public static function url_is_dir($url){
        $is_dir= false;
        $check_url = preg_replace('/\/$/', '' ,$url);
        $header = self::get_headers_curl($check_url);
        if (preg_match('/^HTTP\/.*\s+301\s/i', $header[0])) {
            $is_dir = true;
        }
        return $is_dir;
    }

    /**
     * httpからの絶対パスを取得
     *
     * @param unknown $path
     *            = "/common/css/site.css"
     * @param unknown $mst
     *            = "http://sample_domain"
     * @param unknown $relativePath
     *            = "/ja/about/"
     * @return string
     */
    public static function getResolvePath($path, $mst, $relativePath)
    {
        $path2 = 'no replaced';
        $dev= false;

        // 変換しない処理
        if (preg_match('/^https?:/', $path)) {
            if($dev) echo '"http"から始まる場合'.PHP_EOL;
            return $path;
        } elseif (preg_match('/^javascript:/i', $path)) {
            if($dev) echo 'javascriptで始まるURL'.PHP_EOL;
            return $path;
        } elseif (preg_match('/^mailto\:/i', $path)) {
            if($dev) echo 'mailtoで始まるURL'.PHP_EOL;
            return $path;
        } elseif (preg_match('/^#/', $path)) {
            if($dev) echo '"#"から始まる場合'.PHP_EOL;
            return $path;
        }

        // 変換する処理
        if (preg_match('/^\.\//', $path)) {
            if($dev) echo '"./"から始まる場合'.PHP_EOL;
            $path2 = $mst . $relativePath . preg_replace('/\.\//', '/', $path);
        } elseif (preg_match('/^\.\.\//', $path)) {
            if($dev) echo '"../"のような場合。'.PHP_EOL;
            $base = $mst . $relativePath;
            $path2 = self::createUri($base, $path);
        } elseif (preg_match('/^\/\//', $path)) {
            if($dev) echo '"//"から始まる場合'.PHP_EOL;
            $path2 = 'http:' . $path;
        } elseif (preg_match('/^\//', $path)) {
            if($dev) echo '"/"から始まる場合';
            if(!preg_match('/^\//', $relativePath) && self::isFile($relativePath)){
              if($dev) echo ' $relativePathが"/"から始まらない && ファイルである'.PHP_EOL;
              $path2 = $mst . self::ext_dirname($path). $relativePath;
            }else{
              if($dev) echo ' ルート相対パス'.PHP_EOL;
              if(self::url_is_dir($mst . $path)){
                //   echo "#is DIR";
                $path2 = $mst . $path;
              }else{
                //   echo "#is_FILE";
                $path2 = $mst . $relativePath;
              }
            }
        } else {
            if($dev) echo '"dir"から始まる場合'.PHP_EOL;
            if(preg_match('/^\//', $relativePath)){
              if(preg_match('/\/$/', $relativePath)){
                $path2 = $mst . $relativePath. $path;
              }else{
                $path2 = $mst . $relativePath.'/'. $path;
              }
            }else{
              $path2 = $mst .'/'. $relativePath. $path;
            }
        }
        if($dev) echo 'getResolvePath():'.$path2.PHP_EOL;
        return $path2;
    }

    /**
     * createUri
     * 相対パスから絶対URLを返します
     *
     * @param string $base
     *            ベースURL（絶対URL）
     * @param string $relational_path
     *            相対パス
     * @return string 相対パスの絶対URL
     * @link http://blog.anoncom.net/2010/01/08/295.html/comment-page-1
     */
    public static function createUri($base, $relationalPath)
    {
        $parse = array(
            "scheme" => null,
            "user" => null,
            "pass" => null,
            "host" => null,
            "port" => null,
            "query" => null,
            "fragment" => null
        );
        $parse = parse_url($base);
        $port = (isset($parse["port"])) ? ':'.$parse["port"] : '';
        if (@strpos($parse["path"], "/", (strlen($parse["path"]) - 1)) !== false) {
            $parse["path"] .= ".";
        }
        if (preg_match("/^https?:\/\//", $relationalPath)) {
            return $relationalPath;
        } elseif (preg_match("/^\/.*$/", $relationalPath)) {
            return $parse["scheme"] . "://" . $parse["host"].$port  . $relationalPath;
        } else {
            $basePath = @explode("/", dirname($parse["path"]));
            $relPath = @explode("/", $relationalPath);
            foreach ($relPath as $relDirName) {
                if ($relDirName == ".") {
                    array_shift($basePath);
                    array_unshift($basePath, "");
                } elseif ($relDirName == "..") {
                    array_pop($basePath);
                    if (count($basePath) == 0) {
                        $basePath = array("");
                    }
                } else {
                    array_push($basePath, $relDirName);
                }
            }
            $path = implode("/", $basePath);
            return $parse["scheme"] . "://" . $parse["host"] . $port. $path;
        }
    }

    /**
     * Zand/Table作成
     *
     * @param unknown $a
     */
    public static function createTable($a)
    {
        $aryWidths = array_values(self::getColumnWidth($a));
        $table = new Zend\Text\Table\Table(array(
            'columnWidths' => $aryWidths
        ));
        $colName = self::getColumnName($a);
        $table->appendRow($colName);
        foreach ($a as $b) {
            $row = new Zend\Text\Table\Row();
            foreach ($colName as $v) {
                $row->appendColumn(new Zend\Text\Table\Column($b[$v]));
            }
            $table->appendRow($row);
        }
        echo $table;
    }

    /**
     * db->feachAllの結果から列名を取得
     *
     * @param $feachAll feachAll
     *            Object;
     */
    public static function getColumnName($feachAll)
    {
        $colName = array_filter(array_keys($feachAll[0]), 'is_string');
        return $colName;
    }

    /**
     * feachAllの結果から列のサイズを取得
     *
     * @param $feachAll feachAll
     *            Object;
     */
    public static function getColumnWidth($feachAll)
    {
        $spacer = 2;
        $aryColName = self::getColumnName($feachAll);
        $lan = count($feachAll);
        $result = array();
        for ($i = 0; $i < $lan; $i ++) {
            foreach ($aryColName as $value) {
                if (! isset($result[$value])) {
                    $result[$value] = 0;
                } else {
                    $result[$value] = max($result[$value], mb_strlen($feachAll[$i][$value]) + $spacer);
                }
            }
        }
        return $result;
    }

    /**
     * 配列の要素に括り文字を追加（デフォルトの括り文字は'"'）
     *
     * @param unknown $array
     * @param array $quote
     */
    public static function arrayQuote($array, $quote = array('"', '"'))
    {
        foreach ($array as $value) {
            // ダブルクォートエスケープ
            $value = str_replace('"', '""', $value);
            $added[] = $quote[0] . $value . $quote[1];
        }
        return $added;
    }

    /**
     * ファイルリスト返却
     *
     * @see Symfony\Component\Finder\Finder;
     * @param unknown $dir
     *            "./dir/"
     * @param unknown $pattern
     *            "*.html"
     * @return NULL[]
     */
    public static function getFileList($dir, $pattern)
    {
        $finder = new Finder();
        $iterator = $finder->in($dir)
            ->
        // ディレクトリを指定
        name($pattern)
            ->
        // ファイル名を指定（ワイルドカードを使用できる）
        files(); // ディレクトリは除外し、ファイルのみ取得

        $list = array();
        foreach ($iterator as $fileinfo) { // $fileinfoはSplFiIeInfoオブジェクト
            $list[] = $fileinfo->getPathname();
        }

        return $list;
    }

    public static function createCSV($a)
    {
        $dt = date("md_His");
        $colName = AsazukeUtil::getColumnName($a);
        $csvName = "output${dt}.csv";
        $AsazukeUtilFile = new AsazukeUtilFile($csvName, true);
        $encoding = AsazukeConf::$csv_format["encoding"];
        $linefeed = AsazukeConf::$csv_format["linefeed"];

        // 列
        $AsazukeUtilFile->out(mb_convert_encoding(implode(',', self::arrayQuote($colName)), $encoding, 'UTF-8') . $linefeed, true);
        // 本体
        foreach ($a as $row) {
            $r = array();
            foreach ($colName as $v) {
                $r[] = $row[$v];
            }
            $AsazukeUtilFile->out(mb_convert_encoding(implode(',', self::arrayQuote($r)), $encoding, 'UTF-8') . $linefeed, true);
        }
        echo <<< EOL

[export csv]
$csvName


EOL;
    }

    /**
     * path中に相対パスが含まれるpathを正規化する。
     * 例）'/ja/lab/themes/xdevice/../../../diary/article/184/' -> '/ja/diary/article/184/'
     *
     * @param unknown $v
     */
    public static function asRealPath($v)
    {
        if (preg_match_all('/\.\./', $v, $matches)) {
            $a = explode('/', $v);
            foreach ($matches[0] as $c) {
                // count($matches)分Loopする
                $i = array_search('..', $a);
                unset($a[($i)]);
                unset($a[($i - 1)]); // 一つ前を削除
                $a = array_values($a);
            }
            if(count($a) > 1){
              $v = implode('/', $a);
            }else{
              $v = '/';
            }
        }
        return $v;
    }

    /**
     * コンテンツスキャンしないファイルリスト
     */
    private static function asazukeFilefilter($path)
    {
        // 画像
        if (preg_match('/\.gif$|\.png$|\.jpg$|\.jpeg$|\.bmp$/i', $path)) {
            // "http"から始まる場合
            return false;
        }
        // 動画
        if (preg_match('/\.flv$|\.swf$|\.mp4$|\.m4v$|\.m4p$|\.3gp$|\.3g2$|\.mov$|\.qt$|\.mpg$|\.mpeg$|\.mpeg2$|\.dat$|\.ts$|\.tp$|\.m2t$|\.m2p$|\.avi$|\.divx$|\.wmv$|\.asf$|\.rm$|\.rmvb$|\.mkv$|\.ogm$|\.ogg$/i', $path)) {
            return false;
        }
        // 音声
        if (preg_match('/\.m4a$|\.acc$|\.mp3$|\.wma$/i', $path)) {
            return false;
        }
        // リソース
        if (preg_match('/\.js$|\.vbs$|\.css$/i', $path)) {
            return false;
        }
        // 書類
        if (preg_match('/\.xml$|\.rtf$|\.rdf$|\.rss$|\.pdf$|\.xls$|\.xlsx$|\.doc$|\.docx$|\.ppt$|\.pptx$/i', $path)) {
            return false;
        }
        // バイナリ
        if (preg_match('/\.zip$|\.lzh$|\.tar$|\.tgz$|\.gz$|\.rar$/i', $path)) {
            return false;
        }
        return true;
    }
    /**
     * ファイルとして判断するか否
     */
    public static function isFile($path)
    {
        // コンテンツ
        if (preg_match('/\.html$|\.htm$|\.shtm$|\.shtml$|\.php$|\.cgi$|\.asp$|\.jsp$|\.js$|\.json$|\.tpl$|\.tmpl$|\.tmp$|\.txt$/i', $path)) {
            return true;
        }elseif(! self::asazukeFilefilter($path)) {
            return true;
        }
        return false;
    }

    /**
    * dirname()の拡張
    * dirname　がdirname('/events/');-> '/' になるので仕様拡張
    */
    public static function ext_dirname($path){
      if (self::array_last(str_split($path)) === '/') {
          // echo "'/'で終わっている場合";
          return $path;
      } else {
          // echo "'/'で終わっていない場合";
          // 拡張子判定
          if(self::isFile($path)){
            return dirname($path) . '/'; // dirname()は末尾に'/'が付かない。
          }else{
            return $path . '/';
          }
      }
    }
    /**
    * Strict Standards: Only variables should be passed by reference対策
    */
    public static function array_last($array)
    {
        return end($array);
    }

    /**
     * Asazukeで扱うファイルの判別
     *
     * @param unknown $path
     * @param unknown $mst
     * @return (bool) false=>"要らない"　true=>"要る"
     */
    public static function asazukefilter($path, $relativePath=null)
    {
        $mst = AsazukeConf::$url;
        $relative = $path;
        if(!is_null($relativePath)){
          $relative = $relativePath;
        }
        $path2 = self::getResolvePath($path, $mst, $relative);
        if ($path === $path2) {
            // TODO 一致しない場合はPickles2には不要なデータとみなす
            return false;
        } elseif (! self::asazukeFilefilter($path)) {
            return false;
        }
        return true;
    }

    /**
     * explodeの$delimiterを複数指定可能にした関数
     * @param unknown $delimiters
     * @param unknown $str
     */
    public static function multipleExplode($delimiters, $str)
    {
        mb_regex_encoding("UTF-8");
        $bom = html_entity_decode("&#feff", ENT_NOQUOTES, "UTF-8");
        $str = mb_ereg_replace($delimiters, "", $str);
        return explode($bom, $str);
    }

    /**
     * レスポンスヘッダーをコロン区切で連想配列に変換する。
     */
    public static function parseHeaders($headers)
    {
        $head = array();
        foreach ($headers as $k => $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1]))
                $head[trim($t[0])] = trim($t[1]);
                else {
                    $head[] = $v;
                }
        }
        return $head;
    }

    public static function http_file_get_contents($url, &$response)
    {
        // echo "Digest認証";
        $response2 = $response; // "Strict Standards: Only variables should be passed by reference" 対策。
        $data = self::curl_file_get_contents($url, $response2);
        $response = $response2;
        return $data;
    }

    public static function curl_file_get_contents($url, &$response, $followlocation=true)
    {
      error_reporting(E_ALL);
      ini_set( 'display_errors','1');
      // echo $url.PHP_EOL;

    //   $post_data = array();
      $options = array(
            CURLOPT_URL            => $url, // 取得する URL 。curl_init() でセッションを 初期化する際に指定することも可能です。
            CURLOPT_HEADER         => true, // 	TRUE を設定すると、ヘッダの内容も出力します。
            CURLOPT_VERBOSE        => false, // TRUE を設定すると、詳細な情報を出力します。
            CURLOPT_RETURNTRANSFER => true, // TRUE を設定すると、curl_exec() の返り値を 文字列で返します。通常はデータを直接出力します。
            CURLOPT_FOLLOWLOCATION => true, // TRUE を設定すると、サーバーが HTTP ヘッダの一部として送ってくる "Location: " ヘッダの内容をたどります
            CURLOPT_MAXREDIRS      =>  10,  // CURLOPT_FOLLOWLOCATIONの辿る最大値、
            CURLOPT_SSL_VERIFYPEER => false,    // for https
            CURLOPT_USERPWD        => AsazukeConf::$authUser . ":" . AsazukeConf::$authPass, // 接続に使用するユーザー名とパスワード。 "[username]:[password]" 形式で指定します。
            CURLOPT_USERAGENT      => AsazukeConf::$userAgent, // UserAgent
            CURLOPT_HTTPAUTH       => CURLAUTH_ANY, // 使用する HTTP 認証方法。以下の中から選びます。 CURLAUTH_BASIC、 CURLAUTH_DIGEST、 CURLAUTH_GSSNEGOTIATE、 CURLAUTH_NTLM、 CURLAUTH_ANY および CURLAUTH_ANYSAFE。
              
            # コレを設定するとgithub.ioでは405になるサーバーがあるので注意 →テストが通らなくなります。
            //   CURLOPT_POST           => true, // TRUE を設定すると、HTTP POST を行います。POST は、application/x-www-form-urlencoded 形式で 行われます。これは一般的な HTML のフォームと同じ形式です。
            //   CURLOPT_POSTFIELDS     => http_build_query($post_data) // TRUE にすると、CURLOPT_POSTFIELDS でのファイルアップロードの際の @ プレフィックスを無効にします。 つまり、@ で始まる値を安全に渡せるようになるということです。 アップロードには CURLFile が使われるでしょう。
      );

      $ch = curl_init();
      curl_setopt_array( $ch, $options);

      try {
        $raw_response  = curl_exec( $ch );

        // validate CURL status
        if(curl_errno($ch))
            throw new \Exception(curl_error($ch), 0);

        // validate HTTP status code (user/password credential issues)
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status_code != 200)
          // 200以外は出力
          self::logW('', '$status_code:'. $status_code. ' $url:'.$url);
      } catch(Exception $ex) {
          if ($ch != null) curl_close($ch);
          throw new \Exception($ex);
      }

      $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      $header = substr($raw_response, 0, $header_size);
      
      $response = self::parseHeaders(self::str2array($header));
      $response['reponse_code'] = $status_code;
      $data = substr($raw_response, $header_size );
      if($data === FALSE){
        $data = '';
      }

      if ($ch != null) curl_close($ch);

      return $data;
    }
}
?>
