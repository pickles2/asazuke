<?php
// router.php
// ex) $ php -S 127.0.0.1:1234 router.php

require('config.php');

//「.htaccess」代理
// AddType application/x-httpd-php .php .html
$inc_file = $_SERVER["SCRIPT_FILENAME"];
//if(PHP_OS === 'Darwin'){
//  $inc_file = $_SERVER["SCRIPT_NAME"];
//}
$path = pathinfo($inc_file);
//var_dump($path);
//exit;

if ($path["extension"] === "html" || $path["extension"] === "htm") {
  header("Content-Type: text/html; charset=UTF-8");
  include ($inc_file);
}else if ($path["extension"] === "css" ) {
  header("Content-Type: text/css; charset=UTF-8");
  include ($inc_file);
}else if ($path["extension"] === "js" ) {
  header("Content-Type: text/javascript; charset=UTF-8");
  include ($inc_file);
}else {
  return FALSE;
}
