<?php
require_once ("./src/Asazuke.php");
require_once ("./src/AsazukeConf.php");
require_once ("./src/AsazukeDB.php");
require_once ("./src/AsazukeSiteScan.php");
require_once ("./src/AsazukeUtil.php");
require_once ("./src/AsazukeUtilConsole.php");
require_once ("./src/AsazukeUtilFile.php");

require_once ("./src/libs/phpQuery-onefile.php");

use Mshiba\Px2lib\Asazuke\AsazukeUtil as AsazukeUtil;
use Mshiba\Px2lib\Asazuke\AsazukeConf as AsazukeConf;

class AsazukeUtilTest extends PHPUnit_Framework_TestCase
{

    private $ctx;

    public function setup()
    {
        $this->ctx = new AsazukeUtil();
    }

    public function testLogV(){
      $txt= "testLogV";
      $path = AsazukeConf::getLogPath();
      unlink($path);
      $this->ctx->logV($txt);
      $this->assertEquals(file_get_contents($path), '[Verbose] '.$txt . " ". "\n");
    }
    public function testLogW(){
      $txt= "testLogW";
      $path = AsazukeConf::getLogPath();
      unlink($path);
      $this->ctx->logW($txt);
      $this->assertEquals(file_get_contents($path), '[Warning] '.$txt . " ". "\n");
    }
    public function testLogE(){
      $txt= "testLogE";
      $path = AsazukeConf::getLogPath();
      $this->expectOutputRegex('/^\[Error\] '.$txt.'.*/'); // 標準出力をテスト
      unlink($path);
      $this->ctx->logE($txt);
      $this->assertEquals(file_get_contents($path), '[Error] '.$txt . " ". "\n");

    }

    /**
     * "1st, 2nd, 3rd, 4th..."を返すテスト
     */
    public function testGetCounting()
    {
        $this->assertEquals($this->ctx->getCounting(1), "1st");
        $this->assertEquals($this->ctx->getCounting(2), "2nd");
        $this->assertEquals($this->ctx->getCounting(3), "3rd");
        $this->assertEquals($this->ctx->getCounting(4), "4th");
        $this->assertEquals($this->ctx->getCounting(11), "11th");
        $this->assertEquals($this->ctx->getCounting(12), "12th");
        $this->assertEquals($this->ctx->getCounting(13), "13th");
        $this->assertEquals($this->ctx->getCounting(21), "21st");
        $this->assertEquals($this->ctx->getCounting(22), "22nd");
        $this->assertEquals($this->ctx->getCounting(23), "23rd");
    }


    /**
     * 改行を削除するテスト
     */
    public function testStripReturn()
    {
        $testData = <<<EOL
A
 B
  C
EOL;
        $this->assertEquals($this->ctx->stripReturn($testData), "A B  C");
        $testData = <<<EOL
  10
 9
8
EOL;
        $this->assertEquals($this->ctx->stripReturn($testData), "  10 98");
    }

    // public static function stripWiteSpace($text)
    // public static function getDatPath($ID, $filepath)
    // public static function str2array($text)
    public function testGetResolvePath(){
    //   $mst = AsazukeConf::$url;
      $mst = 'http://misak1.github.io';

      {
        $relativePath = '';
        // 変換しない処理
        $this->assertEquals($this->ctx->getResolvePath('http://google.co.jp', $mst, $relativePath), 'http://google.co.jp');
        $this->assertEquals($this->ctx->getResolvePath('https://google.co.jp', $mst, $relativePath), 'https://google.co.jp');
        $this->assertEquals($this->ctx->getResolvePath('javascript:window.alert("test");', $mst, $relativePath), 'javascript:window.alert("test");');
        $this->assertEquals($this->ctx->getResolvePath('mailto:test@example.jp', $mst, $relativePath), 'mailto:test@example.jp');
        $this->assertEquals($this->ctx->getResolvePath('#TOP', $mst, $relativePath), '#TOP');
        // 変換する処理
        $this->assertEquals($this->ctx->getResolvePath('./html-practice/', $mst, $relativePath), $mst.$relativePath.'/html-practice/');
        $this->assertEquals($this->ctx->getResolvePath('../html-practice/', $mst, $relativePath), $mst.'/html-practice/');
        $this->assertEquals($this->ctx->getResolvePath('//www.pxt.jp/index.html', $mst, $relativePath), 'http://www.pxt.jp/index.html');
        $this->assertEquals($this->ctx->getResolvePath('/html-practice/', $mst, $relativePath), $mst.'/html-practice/');
        $this->assertEquals($this->ctx->getResolvePath('html-practice/', $mst, $relativePath), $mst.'/html-practice/');
      }
      {
        $relativePath = '/';
        // 変換しない処理
        $this->assertEquals($this->ctx->getResolvePath('http://google.co.jp', $mst, $relativePath), 'http://google.co.jp');
        $this->assertEquals($this->ctx->getResolvePath('https://google.co.jp', $mst, $relativePath), 'https://google.co.jp');
        $this->assertEquals($this->ctx->getResolvePath('javascript:window.alert("test");', $mst, $relativePath), 'javascript:window.alert("test");');
        $this->assertEquals($this->ctx->getResolvePath('mailto:test@example.jp', $mst, $relativePath), 'mailto:test@example.jp');
        $this->assertEquals($this->ctx->getResolvePath('#TOP', $mst, $relativePath), '#TOP');
        // 変換する処理
        $this->assertEquals($this->ctx->getResolvePath('./html-practice/', $mst, $relativePath), $mst.$relativePath.'/html-practice/');
        $this->assertEquals($this->ctx->getResolvePath('../html-practice/', $mst, $relativePath), $mst.'/html-practice/');
        // $this->assertEquals($this->ctx->getResolvePath('//misak1.github.io/index.html', $mst, $relativePath), 'http://misak1.github.io/index.html'); // github.ioがルート直下にファイルを置くことを許可していないため。 
        $this->assertEquals($this->ctx->getResolvePath('//misak1.github.io/html-practice/index.html', $mst, $relativePath), 'http://misak1.github.io/html-practice/index.html');
        $this->assertEquals($this->ctx->getResolvePath('/html-practice/', $mst, $relativePath), $mst.'/html-practice/');
        $this->assertEquals($this->ctx->getResolvePath('html-practice/', $mst, $relativePath), $mst.'/html-practice/');
      }
      {
        $relativePath = '/test1';
        // 変換しない処理
        $this->assertEquals($this->ctx->getResolvePath('http://google.co.jp', $mst, $relativePath), 'http://google.co.jp');
        $this->assertEquals($this->ctx->getResolvePath('https://google.co.jp', $mst, $relativePath), 'https://google.co.jp');
        $this->assertEquals($this->ctx->getResolvePath('javascript:window.alert("test");', $mst, $relativePath), 'javascript:window.alert("test");');
        $this->assertEquals($this->ctx->getResolvePath('mailto:test@example.jp', $mst, $relativePath), 'mailto:test@example.jp');
        $this->assertEquals($this->ctx->getResolvePath('#TOP', $mst, $relativePath), '#TOP');
        // 変換する処理
        $this->assertEquals($this->ctx->getResolvePath('./html-practice/', $mst, $relativePath), $mst.$relativePath.'/html-practice/');
        $this->assertEquals($this->ctx->getResolvePath('../html-practice/', $mst, $relativePath), $mst.'/html-practice/');
        $this->assertEquals($this->ctx->getResolvePath('//www.pxt.jp/index.html', $mst, $relativePath), 'http://www.pxt.jp/index.html');
        $this->assertEquals($this->ctx->getResolvePath('/html-practice/', $mst, $relativePath), $mst.'/html-practice/');
        $this->assertEquals($this->ctx->getResolvePath('html-practice/', $mst, $relativePath), $mst.$relativePath.'/html-practice/');
      }
      {
        $relativePath = '/test1/test2';
        // 変換しない処理
        $this->assertEquals($this->ctx->getResolvePath('http://google.co.jp', $mst, $relativePath), 'http://google.co.jp');
        $this->assertEquals($this->ctx->getResolvePath('https://google.co.jp', $mst, $relativePath), 'https://google.co.jp');
        $this->assertEquals($this->ctx->getResolvePath('javascript:window.alert("test");', $mst, $relativePath), 'javascript:window.alert("test");');
        $this->assertEquals($this->ctx->getResolvePath('mailto:test@example.jp', $mst, $relativePath), 'mailto:test@example.jp');
        $this->assertEquals($this->ctx->getResolvePath('#TOP', $mst, $relativePath), '#TOP');
        // 変換する処理
        $this->assertEquals($this->ctx->getResolvePath('./html-practice/', $mst, $relativePath), $mst.$relativePath.'/html-practice/');
        $this->assertEquals($this->ctx->getResolvePath('../html-practice/', $mst, $relativePath), $mst.'/html-practice/');
        $this->assertEquals($this->ctx->getResolvePath('//www.pxt.jp/index.html', $mst, $relativePath), 'http://www.pxt.jp/index.html');
        $this->assertEquals($this->ctx->getResolvePath('/html-practice/', $mst, $relativePath), $mst.'/html-practice/');
        $this->assertEquals($this->ctx->getResolvePath('html-practice/', $mst, $relativePath), $mst.$relativePath.'/html-practice/');
      }

      {
        // ファイル名で終わり、ファイル名だけの相対パス
        $relationalPath = '/madam/brass/check/Forth/smooth/hint/Barber/revolution/lady.html';
        $relativePath = 'percentage.html';
        $this->assertEquals($this->ctx->getResolvePath($relationalPath, $mst, $relativePath), $mst.'/madam/brass/check/Forth/smooth/hint/Barber/revolution/percentage.html');

        // ファイル名で終わり、ルート相対パス
        $relationalPath = '/madam/brass/check/Forth/smooth/hint/Barber/revolution/lady.html';
        $relativePath = '/madam/brass/check/Forth/smooth/hint/Barber/revolution/technology/eye.html';
        $this->assertEquals($this->ctx->getResolvePath($relationalPath, $mst, $relativePath), $mst.'/madam/brass/check/Forth/smooth/hint/Barber/revolution/technology/eye.html');
      }
    }
    // public static function createUri($base, $relationalPath)
    public function testCreateUri(){
      $base = 'http://localhost';
      $relationalPath = 'http://localhost';
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $relationalPath);
      $relationalPath = 'https://localhost';
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $relationalPath);
      $relationalPath = ".";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base);
      $relationalPath = "/";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/');
      $relationalPath = "./test1";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1');
      $relationalPath = "./test1/";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/');
      $relationalPath = "./test1/test2";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2');
      $relationalPath = "./test1/test2/";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2/');
      $relationalPath = "/test1";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1');
      $relationalPath = "/test1/";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/');
      $relationalPath = "/test1/test2";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2');
      $relationalPath = "/test1/test2/";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2/');
      // パス中に相対パスが含まれている場合
      $relationalPath = "./test1/..";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base);
      $relationalPath = "./test1/../";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/');
      $relationalPath = "./test1/test2/..";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1');
      $relationalPath = "./test1/test2/../";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/');
      $relationalPath = "./test1/test2/.";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2');
      $relationalPath = "./test1/test2/./";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2/');

      $base = 'http://localhost:9000';
      $relationalPath = 'http://localhost';
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $relationalPath);
      $relationalPath = 'https://localhost';
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $relationalPath);
      $relationalPath = ".";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base);
      $relationalPath = "/";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/');
      $relationalPath = "./test1";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1');
      $relationalPath = "./test1/";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/');
      $relationalPath = "./test1/test2";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2');
      $relationalPath = "./test1/test2/";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2/');
      $relationalPath = "/test1";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1');
      $relationalPath = "/test1/";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/');
      $relationalPath = "/test1/test2";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2');
      $relationalPath = "/test1/test2/";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2/');
      // パス中に相対パスが含まれている場合
      $relationalPath = "./test1/..";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base);
      $relationalPath = "./test1/../";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/');
      $relationalPath = "./test1/test2/..";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1');
      $relationalPath = "./test1/test2/../";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/');
      $relationalPath = "./test1/test2/.";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2');
      $relationalPath = "./test1/test2/./";
      $this->assertEquals($this->ctx->createUri($base, $relationalPath), $base.'/test1/test2/');
    }
    // public static function createTable($a)
    // public static function getColumnName($feachAll)
    // public static function getColumnWidth($feachAll)
    // public static function arrayQuote($array, $quote = array('"', '"'))
    // public static function getFileList($dir, $pattern)
    // public static function createCSV($a)
    // public static function asRealPath($v)
    public function testAsRealPath(){
      $this->assertEquals($this->ctx->asRealPath('/ja/lab/themes/xdevice/../diary/article/184/'), '/ja/lab/themes/diary/article/184/');
      $this->assertEquals($this->ctx->asRealPath('/ja/lab/themes/xdevice/../../diary/article/184/'), '/ja/lab/diary/article/184/');
      $this->assertEquals($this->ctx->asRealPath('/ja/lab/themes/xdevice/../../../diary/article/184/'), '/ja/diary/article/184/');
      $this->assertEquals($this->ctx->asRealPath('/ja/lab/themes/../../../diary/article/184/'), '/diary/article/184/');
      $this->assertEquals($this->ctx->asRealPath('/ja/lab/../xdevice/../diary/article/184/'), '/ja/diary/article/184/');
      $this->assertEquals($this->ctx->asRealPath('/test1/..'), '/');
    }
    // private staticメソッドのテスト
    public function testAsazukeFilefilter(){
      $class = new AsazukeUtil();
      $method = new ReflectionMethod(get_class($class), 'asazukeFilefilter');
      $method->setAccessible(true);

      // コンテンツ
      $this->assertTrue($method->invoke($class, 'test.html'));
      $this->assertTrue($method->invoke($class, 'test.htm' ));
      $this->assertTrue($method->invoke($class, 'test.shtml'));
      $this->assertTrue($method->invoke($class, 'test.shtm'));
      $this->assertTrue($method->invoke($class, 'test.asp' ));
      $this->assertTrue($method->invoke($class, 'test.jsp' ));
      $this->assertTrue($method->invoke($class, 'test.cgi' ));
      $this->assertTrue($method->invoke($class, 'test.php' ));
      $this->assertTrue($method->invoke($class, 'test.md'  ));
      // 画像
      $this->assertFalse($method->invoke($class, 'test.gif'));
      $this->assertFalse($method->invoke($class, 'test.png'));
      $this->assertFalse($method->invoke($class, 'test.jpg'));
      $this->assertFalse($method->invoke($class, 'test.jpeg'));
      $this->assertFalse($method->invoke($class, 'test.bmp'));
      // 動画
      $this->assertFalse($method->invoke($class, 'test.flv'));
      $this->assertFalse($method->invoke($class, 'test.swf'));
      $this->assertFalse($method->invoke($class, 'test.mp4'));
      $this->assertFalse($method->invoke($class, 'test.m4v'));
      $this->assertFalse($method->invoke($class, 'test.m4p'));
      $this->assertFalse($method->invoke($class, 'test.3gp'));
      $this->assertFalse($method->invoke($class, 'test.3g2'));
      $this->assertFalse($method->invoke($class, 'test.mov'));
      $this->assertFalse($method->invoke($class, 'test.qt'));
      $this->assertFalse($method->invoke($class, 'test.mpg'));
      $this->assertFalse($method->invoke($class, 'test.mpeg'));
      $this->assertFalse($method->invoke($class,'test.mpeg2'));
      $this->assertFalse($method->invoke($class, 'test.dat'));
      $this->assertFalse($method->invoke($class, 'test.ts'));
      $this->assertFalse($method->invoke($class, 'test.tp'));
      $this->assertFalse($method->invoke($class, 'test.m2t'));
      $this->assertFalse($method->invoke($class, 'test.tp'));
      $this->assertFalse($method->invoke($class, 'test.m2p'));
      $this->assertFalse($method->invoke($class, 'test.avi'));
      $this->assertFalse($method->invoke($class, 'test.divx'));
      $this->assertFalse($method->invoke($class, 'test.wmv'));
      $this->assertFalse($method->invoke($class, 'test.asf'));
      $this->assertFalse($method->invoke($class, 'test.rm'));
      $this->assertFalse($method->invoke($class, 'test.rmvb'));
      $this->assertFalse($method->invoke($class, 'test.mkv'));
      $this->assertFalse($method->invoke($class, 'test.ogm'));
      $this->assertFalse($method->invoke($class, 'test.ogg'));
      // 音声
      $this->assertFalse($method->invoke($class, 'test.m4a'));
      $this->assertFalse($method->invoke($class, 'test.acc'));
      $this->assertFalse($method->invoke($class, 'test.mp3'));
      $this->assertFalse($method->invoke($class, 'test.wma'));
      // リソース
      $this->assertFalse($method->invoke($class, 'test.js' ));
      $this->assertFalse($method->invoke($class, 'test.vbs'));
      $this->assertFalse($method->invoke($class, 'test.css'));
      // 書類
      $this->assertFalse($method->invoke($class, 'test.xml'));
      $this->assertFalse($method->invoke($class, 'test.rtf'));
      $this->assertFalse($method->invoke($class, 'test.rdf'));
      $this->assertFalse($method->invoke($class, 'test.rss'));
      $this->assertFalse($method->invoke($class, 'test.pdf'));
      // $this->assertFalse($method->invoke($class, 'test.xls'));
      $this->assertFalse($method->invoke($class, 'test.xlsx'));
      $this->assertFalse($method->invoke($class, 'test.doc'));
      $this->assertFalse($method->invoke($class, 'test.docx'));
      $this->assertFalse($method->invoke($class, 'test.ppt'));
      $this->assertFalse($method->invoke($class, 'test.pptx'));
      // バイナリ
      $this->assertFalse($method->invoke($class, 'test.zip'));
      $this->assertFalse($method->invoke($class, 'test.lzh'));
      $this->assertFalse($method->invoke($class, 'test.tar'));
      $this->assertFalse($method->invoke($class, 'test.tgz'));
      $this->assertFalse($method->invoke($class, 'test.gz'));
      $this->assertFalse($method->invoke($class, 'test.rar'));
    }
    public function testAsazukefilter(){
      $this->assertTrue($this->ctx->asazukefilter('./test.html'));
    }

    public function testExt_dirname()
    {

      $this->assertEquals(dirname('/events/'), '/');
      $this->assertEquals(dirname('/events/1st/'), '/events');
      $this->assertEquals(dirname('/'), '/');
      $this->assertEquals(dirname('/events/index.html'), '/events');
      $this->assertEquals(dirname('/events/aaaa'), '/events');

      $this->assertEquals($this->ctx->ext_dirname('/events/'), '/events/');
      $this->assertEquals($this->ctx->ext_dirname('/events/1st/'), '/events/1st/');
      $this->assertEquals($this->ctx->ext_dirname('/'), '/');
      $this->assertEquals($this->ctx->ext_dirname('/events/index.html'), '/events/');
      $this->assertEquals($this->ctx->ext_dirname('/events/aaaa'), '/events/aaaa/');
    }

    // public static function multipleExplode($delimiters, $str)
    // public static function parseHeaders($headers)
    // public static function http_file_get_contents($url, &$response)
    // public static function curl_file_get_contents($url, &$response)

    /**
     * datファイルのファイルパスを取得するテスト
     */
    public function testGetDatPath()
    {
        $dat = AsazukeConf::getDat();
        $dir = dirname($dat);

        $this->assertEquals(basename($this->ctx->getDatPath(1, $dat)), "1.dat"); // ファイル名チェック
        $this->assertEquals(dirname($this->ctx->getDatPath(1, $dat)), $dir); // ファイルパス

        $test = $this->ctx->getDatPath(2, $dat);
        $this->assertEquals(basename($test), "2.dat"); // ファイル名チェック
        $this->assertEquals(dirname($test), $dir); // ファイルパス
    }

    // http_file_get_contents のテスト
    // 通常, ベーシック認証、ダイジェスト認証
    // 2XX, 3XX, 400, 500のテスト
    public function testHttp_file_get_contents(){
        // テスター：https://httpstatus.io/
        // テストページ：http://httpstat.us/
        AsazukeConf::$authUser = "test";
        AsazukeConf::$authPass = "test";
        /*******
         * 2XX
         *******/
        // 200
        $url = 'http://httpstat.us/200';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 200 OK');
        $this->assertEquals($response['reponse_code'], 200);
        // 202
        $url = 'http://httpstat.us/202';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 202 Accepted');
        $this->assertEquals($response['reponse_code'], 202);
        // 204
        $url = 'http://httpstat.us/204';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 204 No Content');
        $this->assertEquals($response['reponse_code'], 204);

        /*******
         * 3XX
         *******/
        // 300
        $url = 'http://httpstat.us/300';
        $data = $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 300 Multiple Choices');
        $this->assertEquals($response['reponse_code'], 300);
        // 301 (301->200)
        $url = 'http://httpstat.us/301';
        $this->ctx->http_file_get_contents($url, $response);
        // var_dump($response);
        $this->assertEquals($response[0], 'HTTP/1.1 301 Moved Permanently');
        $this->assertEquals($response['reponse_code'], 200);
        // 302 (302->200)
        $url = 'http://httpstat.us/302';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 302 Found');
        $this->assertEquals($response['reponse_code'], 200);
        // 307 (307->200)
        $url = 'http://httpstat.us/307';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 307 Temporary Redirect');
        $this->assertEquals($response['reponse_code'], 200);

        /*******
         * 4XX
         *******/
        // 400
        $url = 'http://httpstat.us/400';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 400 Bad Request');
        $this->assertEquals($response['reponse_code'], 400);
        // 401
        $url = 'http://httpstat.us/401';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 401 Unauthorized');
        $this->assertEquals($response['reponse_code'], 401);
        // 402
        $url = 'http://httpstat.us/402';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 402 Payment Required');
        $this->assertEquals($response['reponse_code'], 402);
        // 403
        $url = 'http://httpstat.us/403';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 403 Forbidden');
        $this->assertEquals($response['reponse_code'], 403);
        // 404
        $url = 'http://httpstat.us/404';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 404 Not Found');
        $this->assertEquals($response['reponse_code'], 404);
        // 405
        $url = 'http://httpstat.us/405';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 405 Method Not Allowed');
        $this->assertEquals($response['reponse_code'], 405);
        // 406
        $url = 'http://httpstat.us/406';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 406 Not Acceptable');
        $this->assertEquals($response['reponse_code'], 406);
        // 407
        $url = 'http://httpstat.us/407';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 407 Proxy Authentication Required');
        $this->assertEquals($response['reponse_code'], 407);
        // 407
        $url = 'http://httpstat.us/408';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 408 Request Timeout');
        $this->assertEquals($response['reponse_code'], 408);

        /*******
         * 5XX
         *******/
        // 500
        $url = 'http://httpstat.us/500';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 500 Internal Server Error');
        $this->assertEquals($response['reponse_code'], 500);
        // 501
        $url = 'http://httpstat.us/501';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 501 Not Implemented');
        $this->assertEquals($response['reponse_code'], 501);
        // 502
        $url = 'http://httpstat.us/502';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 502 Bad Gateway');
        $this->assertEquals($response['reponse_code'], 502);
        // 503
        $url = 'http://httpstat.us/503';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 503 Service Unavailable');
        $this->assertEquals($response['reponse_code'], 503);
        // 504
        $url = 'http://httpstat.us/504';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 504 Gateway Timeout');
        $this->assertEquals($response['reponse_code'], 504);
        // 505
        $url = 'http://httpstat.us/505';
        $this->ctx->http_file_get_contents($url, $response);
        $this->assertEquals($response[0], 'HTTP/1.1 505 HTTP Version Not Supported');
        $this->assertEquals($response['reponse_code'], 505);

    }
}
