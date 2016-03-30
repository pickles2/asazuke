-- テーブル一覧
.header off
-- 表示フォーマット
.mode column
.width 0

-- テーブル一覧(sqliteにはDUAL表がないのでSELECTをechoのかわりに使ってみた。)
SELECT 'テーブル一覧'; 
SELECT name FROM sqlite_master WHERE type = 'table';
SELECT ''; 

-- マルチバイト文字だと幅合わせがうまくいかない。
SELECT 'テーブル名                    ', '  件数';
SELECT '------------------------------', '----------';
SELECT 't_asazukeSS(サイトスキャン)       ', COUNT(*) FROM t_asazukeSS;
SELECT 't_asazuke  (処理済みデータ)       ', COUNT(*) FROM t_asazuke;