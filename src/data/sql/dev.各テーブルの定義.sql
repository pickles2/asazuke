-- 各テーブルの定義
.header off
-- 表示フォーマット
.mode column
.width 0

SELECT 't_asazukeSS';
SELECT '----------------------------------';
SELECT sql FROM sqlite_master WHERE type ='table' AND name = 't_asazukeSS';
SELECT '';

SELECT 't_asazuke';
SELECT '----------------------------------';
SELECT sql FROM sqlite_master WHERE type ='table' AND name = 't_asazuke';
SELECT '';