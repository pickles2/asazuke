-- ステータスコード200以外
.header on
-- 表示フォーマット
.mode column
.width 0

SELECT
  id, fullPath, status
FROM
   t_asazukeSS
WHERE
   statusCode <> 200
ORDER BY
   fullPath
;