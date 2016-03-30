-- テーブル一覧
.header on
-- 表示フォーマット
.mode column
.width 0

SELECT name AS 'テーブル一覧' FROM sqlite_master WHERE type = 'table';