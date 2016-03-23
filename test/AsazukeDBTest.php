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

class AsazukeDBTest extends PHPUnit_Framework_TestCase
{

    private $ctx, $ss;

    public function setup()
    {
        try {
            echo '[AsazukeDB]' . "\n";
            $this->ctx = new AsazukeDB();
        } catch (Exception $e) {}
    }

    public function testSelect()
    {
      $this->ctx->truncate('t_asazukeSS');
      $this->assertEquals(count($this->ctx->select()), 0);
    }
}
