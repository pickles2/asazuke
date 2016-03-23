<?php
namespace Mshiba\Px2lib\Asazuke;

class AsazukeUtilFile
{

    private $file = 'Asazuke.log';

    public function __construct($filePath = '', $isClear = false)
    {
        if ($filePath === '') {
            $D = dirname(__FILE__);
            $this->file = $D . '/' . $this->file;
        } else {
            $this->file = $filePath;
        }
        if ($isClear) {
            // 空ファイル作成
            file_put_contents($this->file, '');
        }
    }

    /**
     * 手動改行
     *
     * @param unknown $contents            
     * @param string $manualLf            
     */
    public function out($contents, $manualLf = false)
    {
        if (! $manualLf) {
            $contents .= "\n";
        }
        file_put_contents($this->file, $contents, FILE_APPEND);
    }

    public function getFileName()
    {
        return $this->file;
    }
}
// $f = new AsazukeUtilFile("../tesst.txt");
// echo $f->getFileName();
// $f->out("aaa");