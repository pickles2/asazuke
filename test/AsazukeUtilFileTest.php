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
use Mshiba\Px2lib\Asazuke\AsazukeUtilFile as AsazukeUtilFile;

class AsazukeUtilFileTest extends PHPUnit_Framework_TestCase
{

    private $ctx;

    public function setup()
    {
        echo '[AsazukeUtilFile]' . PHP_EOL;
        // $this->ctx = new AsazukeUtilFile();
    }

    /**
     * 出力先テスト
     */
    public function testGetFileName()
    {
        // デフォルトの挙動
        $this->ctx = new AsazukeUtilFile();
        echo $this->ctx->getFileName() . PHP_EOL;
        $this->assertEquals(basename($this->ctx->getFileName()), "Asazuke.log");
        
        // ファイル名変更
        $this->ctx = new AsazukeUtilFile('AAA.log');
        echo $this->ctx->getFileName() . PHP_EOL;
        $this->assertEquals(basename($this->ctx->getFileName()), "AAA.log");
        
        // 相対パス指定
        $this->ctx = new AsazukeUtilFile('../BBB.log');
        echo $this->ctx->getFileName() . PHP_EOL;
        $this->assertEquals(basename($this->ctx->getFileName()), "BBB.log");
        
        // 相対パス指定
        $this->ctx = new AsazukeUtilFile('/TMP/CCC.log');
        echo $this->ctx->getFileName() . PHP_EOL;
        $this->assertEquals(basename($this->ctx->getFileName()), "CCC.log");
    }

    /**
     * 書き込みテスト
     */
    public function testOut()
    {
        $file = "AsazukeUtilFile.log";
        $txt = "DDD\nDDD";
        
        // 自動改行OFF
        $this->ctx = new AsazukeUtilFile($file, true);
        $this->ctx->out($txt, true);
        $this->assertEquals(file_get_contents($this->ctx->getFileName()), $txt);
        
        // 自動改行ON
        $this->ctx = new AsazukeUtilFile($file, true);
        $this->ctx->out($txt);
        $this->assertEquals(file_get_contents($this->ctx->getFileName()), $txt . "\n");
        
        // 追記モード
        $this->ctx->out($txt);
        $this->assertEquals(file_get_contents($this->ctx->getFileName()), str_repeat($txt . "\n", 2));
        
        // ファイル削除
        unlink($this->ctx->getFileName());
    }
}
