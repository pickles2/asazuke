-- 元データ削除
DELETE FROM t_asazukeSS;
commit;

-- サンプルデータ
-- INSERT INTO t_asazukeSS (fullPath) VALUES  ('/index.html');
-- INSERT INTO t_asazukeSS (fullPath) VALUES  ('/404.html');

-- 適当な値を埋める
UPDATE t_asazukeSS SET	checkCount = 1, status='HTTP/1.1 401 Authorization Required', statusCode=200;

-- 忘れずに
commit;