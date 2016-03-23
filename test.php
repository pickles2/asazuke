<?php
// $v = '//entry/2014/04//24/005845';
// $v = preg_replace('#//#', '/', $v);
// echo $v;
// exit();
//
// // $path = realpath(".");
// // echo "\n絶対パス：" . $path;
// $v = '/ja/lab/themes/xdevice/../../../diary/article/184/';
// if (preg_match_all('#\.\.\/#', $v, $matches)) {
//     echo $v . "\n";
//     // var_dump($matches);
//     $a = explode('/', $v);
//     foreach ($matches[0] as $c) {
//         // count($matches)分Loopする
//         $i = array_search('..', $a);
//         unset($a[($i)]);
//         unset($a[($i - 1)]); // 一つ前を削除
//         $a = array_values($a);
//         // var_dump($a);
//     }
//     echo implode('/', $a);
// }
// $startPath = '/events/index.html';

$path = '/picture/excess/raw/cheer/ax/one/roll/sheep/TRUE/grasp.html';
$aryPath2 = $aryPath = explode('/', $path);
foreach ($aryPath as $expPath) {
  echo implode('/', $aryPath2).PHP_EOL;
  array_pop($aryPath2);
}
exit;



// $startPath = '/events/dream_car_art_contest/newsroom/gallery20150826_1.html?TB_iframe=true';
// if(preg_match('/\?/', $startPath) == 1){
//   echo explode('?', $startPath)[0];
// }else{
//   echo "not found.";
// }

// echo dirname($startPath);
exit;

if (preg_match('/3\d{2}/', "N")) {
  echo "\nN";
}
if (preg_match('/3\d{2}/', "300")) {
  echo "\n300";
}
if (preg_match('/3\d{2}/', "301")) {
  echo "\n301";
}
if (preg_match('/3\d{2}/', "302")) {
  echo "\n302";
}
if (preg_match('/3\d{2}/', "303")) {
  echo "\n303";
}
if (preg_match('/3\d{2}/', "304")) {
  echo "\n304";
}
if (preg_match('/3\d{2}/', "305")) {
  echo "\n305";
}
if (preg_match('/3\d{2}/', "306")) {
  echo "\n306";
}
if (preg_match('/3\d{2}/', "307")) {
  echo "\n307";
}

if (preg_match('/3\d{2}/', "310")) {
  echo "\n310";
}
if (preg_match('/3\d{2}/', "311")) {
  echo "\n311";
}
if (preg_match('/3\d{2}/', "312")) {
  echo "\n312";
}
if (preg_match('/3\d{2}/', "313")) {
  echo "\n313";
}
if (preg_match('/3\d{2}/', "314")) {
  echo "\n314";
}
if (preg_match('/3\d{2}/', "315")) {
  echo "\n315";
}
if (preg_match('/3\d{2}/', "316")) {
  echo "\n316";
}
if (preg_match('/3\d{2}/', "317")) {
  echo "\n317";
}


if (preg_match('/3\d{2}/', "400")) {
  echo "\n400";
}
if (preg_match('/3\d{2}/', "401")) {
  echo "\n401";
}
if (preg_match('/3\d{2}/', "402")) {
  echo "\n402";
}
if (preg_match('/3\d{2}/', "403")) {
  echo "\n403";
}
if (preg_match('/3\d{2}/', "404")) {
  echo "\n404";
}
if (preg_match('/3\d{2}/', "405")) {
  echo "\n405";
}
if (preg_match('/3\d{2}/', "406")) {
  echo "\n406";
}
if (preg_match('/3\d{2}/', "407")) {
  echo "\n407";
}

echo sprintf("レスポンスコード%03d", 399);


$a = array(
  0=>"HTTP/1.1 307 Temporary Redirect"
,  1=>"Cache-Control: private"
,  2=>"Content-Length: 22"
,  3=> "Content-Type: text/plain; charset=utf-8"
,  4=>"Location: http://httpstat.us"
,  5=>"Server: Microsoft-IIS/8.0"
,  6=>"X-AspNetMvc-Version: 5.1"
,  7=>"X-AspNet-Version: 4.0.30319"
,  8=>"X-Powered-By: ASP.NET"
,  9=>"Access-Control-Allow-Origin: *"
);
var_dump($a);
foreach ($a as $idx => $header) {
  if(preg_match('/^Location\:/i', $header, $matches)){
    echo str_replace($matches, '', $header).PHP_EOL;
  }
}

var_dump(count(array()));

?>
