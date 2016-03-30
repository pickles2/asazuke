-- サイトスキャンテーブルデータ
.header on
-- 表示フォーマット
.mode column
.width 4 50 5 10 20 10 20

SELECT
*
FROM
   t_asazukeSS
ORDER BY
   fullPath
;