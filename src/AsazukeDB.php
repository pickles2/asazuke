<?php
namespace Mshiba\Px2lib\Asazuke;

class AsazukeDB
{

    // private $file_db ; // phpunitのためにコメントアウト

    /**
     * Close file db connection
     */
    public function __destruct()
    {
        try {
            $this->file_db = null;
        } catch (\PDOException $e) {
            // Print PDOException message
            echo $e->getMessage();
        }
    }

    /**
     * コンストラクタ
     * 初期化・変数定義
     * 1.Create databases
     * 2.Open connections
     */
    public function __construct()
    {

        // date_default_timezone_set('Asia/Tokyo');
        date_default_timezone_set(AsazukeConf::$timezone);

        try {
            // Create (connect to) SQLite database in file
            // $this->file_db = new \PDO('sqlite:' . __DIR__ . '/data/asazuke.sqlite');
            $this->file_db = new \PDO('sqlite:' . AsazukeConf::getDbFile());
            // Set errormode to exceptions
            $this->file_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->create();
            // 表の作成と初期化
            $this->createAsazuke();
        } catch (\PDOException $e) {
            // Print PDOException message
            echo $e->getMessage();
        }
    }

    public function create()
    {
        $sql = <<<EOD
CREATE TABLE IF NOT EXISTS t_asazukeSS (
    id INTEGER PRIMARY KEY,
    fullPath TEXT UNIQUE,
    depth INTEGER,
    checkCount INTEGER,
    status TEXT,
    statusCode INTEGER,
    time default CURRENT_TIMESTAMP
)
EOD;
        $this->file_db->exec($sql);
    }

    public function createXth($i)
    {
        $tableName = "t_" . AsazukeUtil::getCounting($i);
        $sql = <<<EOD
CREATE TABLE IF NOT EXISTS ${tableName} (
    id INTEGER PRIMARY KEY,
    path TEXT,
    parentID INTEGER,
    time default CURRENT_TIMESTAMP
)
EOD;
        $this->file_db->exec($sql);
    }

    /**
     * Select all data from memory db messages table
     *
     * <pre>
     * <b>Usage</b>
     * $this->select();
     * $this->select('id = 5');
     * $this->select('errorCount < 4');
     * $this->select('id > 5', 'title LIKE "%TITLE%"');
     * <pre>
     */
    public function select()
    {
        $sql = <<<EOD
SELECT * FROM t_asazukeSS WHERE 1=1
EOD;
        $args = func_get_args();
        foreach ($args as $k => $v) {
            $sql .= ' AND ' . $v;
        }
        AsazukeUtil::logV("SQL", $sql);
        $stmt = $this->file_db->query($sql);
        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Statement failed: " . $e->getMessage();
            return false;
        }
        $result = $stmt->fetchAll();
        return $result;
    }

    /**
     * サイトスキャンの進捗を返す。
     * @return boolean|string
     */
    public function getSiteScanProgress()
    {
        $sql = <<<EOD
SELECT T1.count || " / " || T2.count AS PROGRESS FROM
(SELECT count(id) AS count FROM t_asazukeSS WHERE checkCount>0) AS T1 LEFT JOIN
(SELECT count(id) AS count FROM t_asazukeSS) AS T2
EOD;
        $stmt = $this->file_db->query($sql);
        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Statement failed: " . $e->getMessage();
            return false;
        }
        $result = $stmt->fetchColumn();
        return $result;
    }

    /**
     * テーブル一覧
     *
     * @return boolean|unknown
     */
    public function tables()
    {
        return $this->query("select tbl_name from sqlite_master where type='table'");
    }

    /**
     * テーブル定義
     *
     * @return boolean|unknown
     */
    public function schema($table_name = '')
    {
        $sql = "SELECT sql FROM sqlite_master WHERE type='table'";
        if ($table_name !== '') {
            $sql .= " AND tbl_name='" . $table_name . "'";
        }
        return $this->query($sql);
    }

    public function query($sql, $fetch_style = \PDO::FETCH_BOTH)
    {
        $stmt = $this->file_db->query($sql);
        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Statement failed: " . $e->getMessage();
            return false;
        }
        $result = $stmt->fetchAll($fetch_style);
        return $result;
    }

    /**
     * Select all data from memory db messages table
     *
     * <pre>
     * <b>Usage</b>
     * $this->selectXth();
     * $this->selectXth('id = 5');
     * $this->selectXth('errorCount < 4');
     * $this->selectXth('id > 5', 'title LIKE "%TITLE%"');
     * <pre>
     */
    public function selectXth($depth)
    {
        $this->createXth($depth);
        $tableName = "t_" . AsazukeUtil::getCounting($depth);
        $sql = <<<EOD
SELECT * FROM ${tableName} WHERE 1=1
EOD;

        $args = func_get_args();
        foreach ($args as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $sql .= ' AND ' . $v;
        }
        AsazukeUtil::logV("SQL", $sql);
        $stmt = $this->file_db->query($sql);
        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Statement failed: " . $e->getMessage();
            return false;
        }
        $result = $stmt->fetchAll();
        return $result;
    }

    /**
     * Prepare INSERT statement to SQLite3 file db
     */
    public function insert($aryData)
    {
        try {
          // var_dump($aryData[0]);
          $fullPath = $aryData[0]['fullPath'];
          $result = $this->select('fullPath=\''+ $fullPath+ '\' limit 1');
          if ($result || count($result) > 0) {
              return 0;
          }
            // 行追加時に、uniqueカラムのデータがかち合ったら追加しない
            // OR IGNORE を使うとuniqueエラーを補足出来ない為、使用禁止
            // INSERT OR IGNORE INTO t_asazukeSS (
            $sql = <<<EOD
INSERT INTO t_asazukeSS (
    fullPath,
    depth,
    checkCount,
    status,
    statusCode
) VALUES (
    :fullPath,
    :depth,
    :checkCount,
    :status,
    :statusCode
)
EOD;

            $stmt = $this->file_db->prepare($sql);

            // Bind parameters to statement variables
            $stmt->bindParam(':fullPath', $fullPath);
            $stmt->bindParam(':depth', $depth);
            $stmt->bindParam(':checkCount', $checkCount);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':statusCode', $statusCode);

            // Loop thru all messages and execute prepared insert statement
            foreach ($aryData as $key) {
                // Set values to bound variables
                $fullPath = $key['fullPath'];
                $depth = $key['depth'];
                $checkCount = $key['checkCount'];
                $status = $key['status'];
                $statusCode = $key['statusCode'];

                // Execute statement
                $stmt->execute();

                // last insert id
                $lastInsertId = $this->file_db->lastInsertId();
                return $lastInsertId;
            }
        } catch (\PDOException $e) {
            echo "Statement failed: " . $e->getMessage();
            return 1;
        }
        return 0;
    }

    /**
     * Prepare INSERT statement to SQLite3 file db
     */
    public function insertXth($depth, $path, $parentID)
    {
        $this->createXth($depth);
        $tableName = "t_" . AsazukeUtil::getCounting($depth);
        $sql = <<<EOD
INSERT INTO ${tableName} (
    path,
    parentID
) VALUES (
    :path,
    :parentID
)
EOD;

        AsazukeUtil::logV("SQL", AsazukeUtil::stripReturn($sql) . " $path $parentID");
        $stmt = $this->file_db->prepare($sql);
        // echo $sql;
        // Bind parameters to statement variables
        $stmt->bindParam(':path', $path);
        $stmt->bindParam(':parentID', $parentID);
        $stmt->execute();

        // last insert id
        $lastInsertId = $this->file_db->lastInsertId();
        return $lastInsertId;
    }

    /**
     * Prepare INSERT statement to SQLite3 file db
     */
    public function updateChecked($id)
    {
        $result = $this->select("id=" . $id);

        $path = $result[0]['fullPath'];
        $currentCount = (int) $result[0]['checkCount'];
        AsazukeUtil::logV("PATH", $path);
        AsazukeUtil::logV("currentCount", $currentCount);

echo '$id:'. "$id"."\n";

        $sql = <<<EOD
UPDATE t_asazukeSS SET
    checkCount = :checkCount
WHERE
    fullPath = :fullPath
EOD;
        // echo AsazukeUtil::stripReturn($sql);
        $stmt = $this->file_db->prepare($sql);

        // Bind parameters to statement variables
        $stmt->bindParam(':checkCount', $checkCount);
        $stmt->bindParam(':fullPath', $fullPath);
        $checkCount = $currentCount + 1;
        $fullPath = $path;
        // Execute statement
        $stmt->execute();

        // last insert id
        $result = $this->file_db->lastInsertId();
        return $result;
    }

    public function createAsazuke()
    {
        $sql = <<<EOD
CREATE TABLE IF NOT EXISTS t_asazuke (
    id INTEGER PRIMARY KEY,
    filePath TEXT
    time default CURRENT_TIMESTAMP
)
EOD;
        $this->file_db->exec($sql);
    }

    /**
     * 表データ削除
     *
     * @param unknown $table
     */
    public function truncate($table)
    {
        // SQLiteはTRUNCATE文をサポートしない為
        // $sql = 'DELETE FROM :tableName'; // table名はbind変数として扱えない
        $sql = sprintf('DELETE FROM %s', $this->file_db->quote($table));
        return $this->file_db->exec($sql);
    }

    /**
     * Select all data from memory db messages table
     *
     * <pre>
     * <b>Usage</b>
     * $this->select();
     * $this->select('id = 5');
     * $this->select('errorCount < 4');
     * $this->select('id > 5', 'title LIKE "%TITLE%"');
     * <pre>
     */
    public function selectAsazuke()
    {
        $sql = <<<EOD
SELECT * FROM t_asazuke WHERE 1=1
EOD;
        $args = func_get_args();
        foreach ($args as $k => $v) {
            $sql .= ' AND ' . $v;
        }
        // echo $sql;
        $stmt = $this->file_db->query($sql);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Statement failed: " . $e->getMessage();
            return false;
        }
        $result = $stmt->fetchAll();
        return $result;
    }

    /**
     * Prepare INSERT statement to SQLite3 file db
     */
    public function insertAsazuke($aryData)
    {
        $sql = <<<EOD
INSERT INTO t_asazuke (
    filePath
) VALUES (
    :filePath
)
EOD;

        $stmt = $this->file_db->prepare($sql);

        // Bind parameters to statement variables
        $stmt->bindParam(':filePath', $filePath);
        // $stmt->bindParam(':message', $message);
        // $stmt->bindParam(':title', $title);
        // $stmt->bindParam(':h1', $h1);
        // $stmt->bindParam(':h2', $h2);
        // $stmt->bindParam(':h3', $h3);
        // $stmt->bindParam(':breadCrumb', $breadCrumb);
        // $stmt->bindParam(':meta', $meta);
        // $stmt->bindParam(':errorCount', $errorCount);
        // $stmt->bindParam(':warningCount', $warningCount);

        // Loop thru all messages and execute prepared insert statement
        foreach ($aryData as $key) {
            // Set values to bound variables
            $filePath = $key['filePath'];
            // $message = $key['message'];
            // $title = $key['title'];
            // $h1 = $key['h1'];
            // $h2 = $key['h2'];
            // $h3 = $key['h3'];
            // $breadCrumb = $key['breadCrumb'];
            // $meta = $key['meta'];
            // $errorCount = $key['errorCount'];
            // $warningCount = $key['warningCount'];

            // Execute statement
            $stmt->execute();

            // last insert id
            $lastInsertId = $this->file_db->lastInsertId();
            return $lastInsertId;
        }
    }
}
?>
