<?php
namespace Mshiba\Px2lib\Asazuke;

class AsazukeInlineCSS
{

    private function nestFunc(&$url, &$buffer)
    {
        // echo "\n new url::". $buffer . "\n";
        $aryData = AsazukeUtil::str2array($buffer);
        
        // $a = array(
        // ' @import url("style32.css");',
        // "@import url('sty212le.css');",
        // '@import url(st3212yle.css);',
        // ' @import "sty23le.css";',
        // " @import 's444tyle.css';",
        // "@media print{",
        // "}",
        // "@import 'styl55e.css';",
        // "color:#000000;"
        // );
        
        foreach ($aryData as $k => $target) {
            // 1.cssをセミコロン区切りで行データに分解
            // 2.@importでフィルタリング<- ココから
            if (strpos($target, '@import') === false) {
                continue;
            }
            
            // 3.' ,()"¥''のいずれかで文字を分解
            // $delimiters = '(。"|？"|！"|。”|？”|！”|?”|!”|。|；|！|？|;|!|?|n)';
            $delimiters = '(\turl| url| |,|\(|\)|\"|\'|;|)';
            $result = AsazukeUtil::multipleExplode($delimiters, $target);
            // var_dump($result);
            
            $css = implode('', $result);
            
            // 4.末尾が.cssのデータを抽出
            // mb_regex_encoding("UTF-8");
            // $str = mb_ereg_replace('@import', '', $css);
            $str = str_replace('@import', '', $css);
            var_dump($css, $str);
            echo "\n";
            // 5.現在のcssパスに抽出したcssを結合して@importしているcssを解析
            
            $info = parse_url($url);
            extract($info);
            var_dump($scheme, $host, $path);
            // echo dirname($path);
            $importURL = $scheme . "://" . $host . dirname($path) . "/" . $str;
            echo $importURL . "\n";
            
            $cssData = AsazukeUtil::http_file_get_contents($importURL, $response);
            var_dump($response);
            $buffer = str_replace($target, $cssData, $buffer);

            exit;
            
            // 5.5 backgroundも処理する
            

            
            // 6.cssを再帰的に処理する。
            $this->nestFunc($url, $buffer);
        }
    }

    public function __construct()
    {
        $url = 'http://www.pxt.jp/caches/p/theme/css/common.css';
        $buffer = file_get_contents($url);
        //
        
        $this->nestFunc($url, $buffer);
        
        echo "lastURL:". $url ."\n";
        echo "lastData:". $buffer."\n";
        // $buffer
        
        /**
         * *********************************
         */
        return 0;
        
        $wd = __DIR__ . '/data/inlineCSS/';
        $packageJson = $wd . "package.json";
        if (! file_exists($packageJson)) {
            echo "${packageJson} が見つかりません。";
        }
        $aryData = json_decode(file_get_contents($packageJson));
        
        // var_dump($a);
        chdir($wd);
        
        $cmd = 'npm ls';
        exec($cmd, $arr, $res);
        
        if ($res === 0) {
            // var_dump($arr, $res);
            // npm インストールチェック
            foreach ($aryData->devDependencies as $key => $value) {
                // echo " ${key}@\n";
                $ret = preg_grep("/ ${key}@/", $arr);
                if (count($ret) == 0) {
                    echo "インストールされていないモジュールがあります。";
                }
                // var_dump($ret);
            }
        } else {
            echo "npmがインストールされていないか。npmが実行に失敗しました。";
            exit(1);
        }
        
        echo "npm チェック通過\n";
        
        $cmd = 'gulp --gulpfile gulp-inline-css.js';
        exec($cmd, $aryData, $resb);
        // var_dump($a, $b);
        if ($resb === 0) {
            echo "gulpタスク完了";
        }
        //
    }
}

// new AsazukeInlineCSS();