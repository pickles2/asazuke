<?php
require_once ("./src/Asazuke.php");
require_once ("./src/AsazukeConf.php");
require_once ("./src/AsazukeDB.php");
require_once ("./src/AsazukeSiteScan.php");
require_once ("./src/AsazukeUtil.php");
require_once ("./src/AsazukeUtilConsole.php");
require_once ("./src/AsazukeUtilFile.php");

require_once ("./src/libs/phpQuery-onefile.php");

use Mshiba\Px2lib\Asazuke\AsazukeSiteScan as AsazukeSiteScan;
use Mshiba\Px2lib\Asazuke\AsazukeDB as AsazukeDB;
use Mshiba\Px2lib\Asazuke\AsazukeMessage as AsazukeMessage;
use Mshiba\Px2lib\Asazuke\AsazukeConf as AsazukeConf;

class AsazukeSiteScanTest extends PHPUnit_Framework_TestCase
{

    private $ctx, $ss;

    public function setup()
    {
        try {
            echo '[AsazukeSiteScan]' . "\n";
            AsazukeConf::$url = 'http://misak1.github.io';
            $this->ctx = new AsazukeSiteScan();
        } catch (Exception $e) {}
    }

    public function testCheckStatusCode()
    {

      $url = 'http://httpstat.us/200';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_2XX);
      $this->assertEquals($html, '200 OK');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/202';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_2XX);
      $this->assertEquals($html, '202 Accepted');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/204';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_2XX);
      $this->assertEquals($html, ''); // 204 No Content
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/300';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_3XX);
      $this->assertEquals($html, '300 Multiple Choices');
      $this->assertEquals($url, 'http://httpstat.us/300');

      $url = 'http://httpstat.us/301';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_2XX);
      // $this->assertEquals($html, ''); // HTMLコンテンツが返る
      $this->assertEquals($url, 'http://httpstat.us/301');

      $url = 'http://httpstat.us/302';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_2XX);
      // $this->assertEquals($html, ''); // HTMLコンテンツが返る
      $this->assertEquals($url, 'http://httpstat.us/302');

      $url = 'http://httpstat.us/307';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_2XX);
      // $this->assertEquals($html, ''); // HTMLコンテンツが返る
      $this->assertEquals($url, 'http://httpstat.us/307');

      $url = 'http://httpstat.us/400';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_4XX);
      $this->assertEquals($html, '400 Bad Request');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/401';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_4XX);
      $this->assertEquals($html, '401 Unauthorized');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/402';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_4XX);
      $this->assertEquals($html, '402 Payment Required');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/403';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_4XX);
      $this->assertEquals($html, '403 Forbidden');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/404';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_4XX);
      $this->assertEquals($html, '404 Not Found');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/405';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_4XX);
      $this->assertEquals($html, '405 Method Not Allowed');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/406';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_4XX);
      $this->assertEquals($html, '406 Not Acceptable');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/407';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_4XX);
      $this->assertEquals($html, '407 Proxy Authentication Required');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/500';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_5XX);
      $this->assertEquals($html, '500 Internal Server Error');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/501';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_5XX);
      $this->assertEquals($html, '501 Not Implemented');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/502';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_5XX);
      $this->assertEquals($html, '502 Bad Gateway');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/503';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_5XX);
      $this->assertEquals($html, '503 Service Unavailable');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/504';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_5XX);
      $this->assertEquals($html, '504 Gateway Timeout');
      $this->assertEquals($url, $url);

      $url = 'http://httpstat.us/505';
      extract( $this->ctx->checkStatusCode($url), EXTR_OVERWRITE);
      $this->assertEquals($statusCode, AsazukeMessage::$CD_5XX);
      $this->assertEquals($html, '505 HTTP Version Not Supported');
      $this->assertEquals($url, $url);

    }
    public function testGetHref()
    {
      $testDataDir = getcwd().'/test/testData/';
      $html = file_get_contents($testDataDir.'picture/excess.html');
      AsazukeConf::$isInsistently = true;
      // echo count($this->ctx->getHref($html));
      $this->assertEquals(count($this->ctx->getHref($html)), 12);
      AsazukeConf::$isInsistently = false;
      // echo count($this->ctx->getHref($html));
      $this->assertEquals(count($this->ctx->getHref($html)), 12);
    }
    public function testGetRootRelativeURLs()
    {
      $testURL = 'http://127.0.0.1';
      $v = "dir1";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), "/".$v);
      $v = "dir1/";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), "/".$v);
      $v = ".";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), "/.");
      $v = "./";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), '/./');
      $v = "./dir1";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), '/./dir1');
      $v = "./dir1/";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), '/./dir1/');

      $testURL = 'http://127.0.0.1:1212';
      $v = "dir1";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), "/".$v);
      $v = "dir1/";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), "/".$v);
      $v = ".";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), "/.");
      $v = "./";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), '/./');
      $v = "./dir1";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), '/./dir1');
      $v = "./dir1/";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), '/./dir1/');

      $url = 'http://127.0.0.1:1212';
      $path = '/test1';
      $testURL = $url.$path;
      $v = "dir1";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path."/".$v);
      $v = "dir1/";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path."/".$v);
      $v = ".";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path."/.");
      $v = "./";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'/./');
      $v = "./dir1";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'/./dir1');
      $v = "./dir1/";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'/./dir1/');
      $v = "..";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'/..');
      $v = "../";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'/../');
      $v = "../dir1";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'/../dir1');
      $v = "../dir1/";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'/../dir1/');

      $url = 'http://127.0.0.1:1212';
      $path = '/test1/';
      $testURL = $url.$path;
      $v = "dir1";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.$v);
      $v = "dir1/";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.$v);
      $v = ".";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.".");
      $v = "./";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'./');
      $v = "./dir1";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'./dir1');
      $v = "./dir1/";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'./dir1/');
      $v = "..";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'..');
      $v = "../";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'../');
      $v = "../dir1";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'../dir1');
      $v = "../dir1/";
      $this->assertEquals($this->ctx->getRootRelativeURLs($testURL, $v), $path.'../dir1/');

    }
    public function testPathClean()
    {
      $path ='/dir1';
      $this->assertEquals($this->ctx->pathClean($path), '/dir1');
      $path ='/dir1/';
      $this->assertEquals($this->ctx->pathClean($path), '/dir1/');
      $path ='/.';
      $this->assertEquals($this->ctx->pathClean($path), '/.');
      $path ='/./';
      $this->assertEquals($this->ctx->pathClean($path), '/');
      $path ='/./dir1';
      $this->assertEquals($this->ctx->pathClean($path), '/dir1');
      $path ='/./dir1/';
      $this->assertEquals($this->ctx->pathClean($path), '/dir1/');
      $path ='/test1/dir1';
      $this->assertEquals($this->ctx->pathClean($path), '/test1/dir1');
      $path ='/test1/dir1/';
      $this->assertEquals($this->ctx->pathClean($path), '/test1/dir1/');
      $path ='/test1/.';
      $this->assertEquals($this->ctx->pathClean($path), '/test1/.');
      $path ='/test1/./';
      $this->assertEquals($this->ctx->pathClean($path), '/test1/');
      $path ='/test1/./dir1';
      $this->assertEquals($this->ctx->pathClean($path), '/test1/dir1');
      $path ='/test1/./dir1/';
      $this->assertEquals($this->ctx->pathClean($path), '/test1/dir1/');
      $path ='/test1/..';
      $this->assertEquals($this->ctx->pathClean($path), '/');
      $path ='/test1/../';
      $this->assertEquals($this->ctx->pathClean($path), '/');
      $path ='/test1/../dir1';
      $this->assertEquals($this->ctx->pathClean($path), '/dir1');
      $path ='/test1/../dir1/';
      $this->assertEquals($this->ctx->pathClean($path), '/dir1/');
    }

    public function testOneFileExec()
    {
        AsazukeConf::$url = 'http://misak1.github.io';
    
        $host = parse_url(AsazukeConf::$url)['host'];
        {
    
            // javascriptスキームのテスト
            {
                $this->assertEquals($this->ctx->oneFileExec('javascript: return void(0);', $unitTestResult), AsazukeMessage::$CD_JS);
                // var_dump($unitTestResult);
                unset($unitTestResult);
                $this->assertEquals($this->ctx->oneFileExec('JAVASCRIPT: return void(0);', $unitTestResult), AsazukeMessage::$CD_JS);
                // var_dump($unitTestResult);
                unset($unitTestResult);
            }
    
            // mailtoスキームのテスト
            {
                $this->assertEquals($this->ctx->oneFileExec('mailto:test@example.jp;', $unitTestResult), AsazukeMessage::$CD_MAILTO);
                // var_dump($unitTestResult);
                unset($unitTestResult);
                $this->assertEquals($this->ctx->oneFileExec('mailto:test@email.com;', $unitTestResult), AsazukeMessage::$CD_MAILTO);
                // var_dump($unitTestResult);
                unset($unitTestResult);
            }
    
            {
                // 404のテスト
                $this->assertEquals($this->ctx->oneFileExec('undefined.html', $unitTestResult), AsazukeMessage::$CD_4XX);
                var_dump($unitTestResult);
                unset($unitTestResult);
            }
    
            // 外部サイト
            {
                // 外部サイトの場合 'http://misak1.github.io/http://www.pxt.jp'形式になるので404として処理される。
                // "//"(ネットワークパス)から始まるURL
                $this->assertEquals($this->ctx->oneFileExec('http://www.pxt.jp', $unitTestResult), AsazukeMessage::$CD_4XX);
                var_dump($unitTestResult);
                var_dump($host);
                unset($unitTestResult);
                // "//"(ネットワークパス)から始まるURL (http://misak1.github.io) // github.ioがルート直下にファイルを置くことを許可していないため。
                $this->assertEquals($this->ctx->oneFileExec('//' . $host, $unitTestResult), AsazukeMessage::$CD_4XX);
                var_dump($unitTestResult);
                unset($unitTestResult);
                // "//"(ネットワークパス)から始まるURL
                $this->assertTrue($this->ctx->oneFileExec('//' . $host. '/html-practice/', $unitTestResult));
                var_dump($unitTestResult);
                unset($unitTestResult);
                // 外部サイト // 戻り値としてはtrueになるが実際には処理されることはない
                $this->assertTrue($this->ctx->oneFileExec('//' . 'www.pxt.jp', $unitTestResult));
                var_dump($unitTestResult);
                unset($unitTestResult);
            }
    
            // DBに投入するデータのテスト
            {
                $this->ctx->oneFileExec('/ja/entry/2016-01-01/', $unitTestResult);
                // var_dump($unitTestResult);
                unset($unitTestResult);
            }
        }

        // 実際に動かすテスト
        // AsazukeConf::$url = 'http://www.example.co.jp';
        // $host = parse_url(AsazukeConf::$url)['host'];
        // {
        //     $this->ctx->oneFileExec('/service/', $unitTestResult);
        //     var_dump($unitTestResult);
        //     $validLinks = array();
        //     $skipLinks = array();
        //     foreach ($unitTestResult['validLinks'] as $val) {
        //         $validLinks[] = $val['fullPath'];
        //     }
        //     var_dump($unitTestResult['skipLinks']);
        //     foreach ($unitTestResult['skipLinks'] as $val) {
        //         $k = $val[1];
        //         $skipLinks[$k] = $val[0];
        //     }
        //     print_r($validLinks);
        //     print_r($skipLinks);
        //     // ajax, pjaxで取得した記事のリンクは処理出来ない。
        // }
        // 重複を削除したリスト
        // jsでページ内のリンクを取得する
        // var ary=[], uniq;$('a').each(function(){ary.push($(this).attr('href'));});uniq=ary.filter(function (x, i, self) {return self.indexOf(x) === i;});console.table(uniq.sort());
    }
}
