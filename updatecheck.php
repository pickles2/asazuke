<?php
/**
 * Gitの更新チェック
 */
exec('git ls-remote origin HEAD', $result1);
$remoteId = substr($result1[0], 0, 40);
exec('git show -s --format=%H', $result2);
$localId = $result2[0];

if($remoteId == $localId){
  echo 0;
}else{
  // 更新あり
  echo -1;
}
