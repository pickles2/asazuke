-- 処理済みデータ
.header on
-- 表示フォーマット
.mode column
.width 0 70 0 0

SELECT
   -- *
  id, filePath, errorCount, warningCount
FROM
   t_asazuke
;