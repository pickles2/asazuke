<?php
require_once ("./src/Asazuke.php");
require_once ("./src/AsazukeConf.php");
require_once ("./src/AsazukeDB.php");
require_once ("./src/AsazukeSiteScan.php");
require_once ("./src/AsazukeUtil.php");
require_once ("./src/AsazukeUtilConsole.php");
require_once ("./src/AsazukeUtilFile.php");

require_once ("./src/libs/phpQuery-onefile.php");

use Mshiba\Px2lib\Asazuke\Asazuke as Asazuke;
class AsazukeTest extends PHPUnit_Framework_TestCase
{
    private $ctx;
    public function setup()
    {
        echo '[Asazuke]'."\n";
        $this->ctx = new Asazuke();
    }
    public function testExec()
    {
        $this->assertTrue(TRUE);
    }

//     public function testMethodName()
//     {
//         $this->assertTrue(TRUE);
//     }
}
