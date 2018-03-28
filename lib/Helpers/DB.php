<?php


namespace SITE\Helpers;


use SITE\Exceptions\DBException;

class DB {

    /** @Inject("db.host") */
    protected $host;
    /** @Inject("db.name") */
    protected $dbname;
    /** @Inject("db.user") */
    protected $dbuser;
    /** @Inject("db.pass") */
    protected $dbpass;

    /** @var int */
    protected $transactionNestingLevel = 0;
    /** @var \PDO */
    protected $db;
    /** @var \PDOStatement */
    public $stmt;
    /** @var array */
    protected $tableToFields = [];

    /**
     * @Inject({"db.host", "db.name", "db.user", "db.pass"})
     * @param string $host
     * @param string $dbname
     * @param string $dbuser
     * @param string $dbpass
     * @throws DBException
     */
    public function __construct($host = null, $dbname = null, $dbuser = null, $dbpass = null) {
        $this->host   = $host;
        $this->dbname = $dbname;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;

        if(!empty($this->host)
            && !empty($this->dbname)
            && !empty($this->dbuser)
            && !empty($this->dbpass)) {
            $this->connect();
        }
    }

    /**
     * @Init
     * @throws DBException
     */
    public function connect() {
        $conStr = 'mysql:host='.$this->host.';dbname='.$this->dbname;
        $options = [
            \PDO::ATTR_PERSISTENT         => Config::getInstance()->environment == 'development' ?  true : false,

            \PDO::ATTR_PERSISTENT         =>  false,
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DRIVER_NAME        => "mysql",
            \PDO::ATTR_EMULATE_PREPARES   => true,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"
        ];
        try {
            $this->db = new \PDO($conStr, $this->dbuser, $this->dbpass, $options);
        } catch (\PDOException $e) {
            throw new DBException($e);
        }
    }

    /**
     * @param string $query
     */
    public function prepare($query) {
        $this->stmt = $this->db->prepare($query);
    }

    /**
     * @param string $param
     * @param mixed $value
     * @param int $type
     */
    public function bind($param, $value, $type = \PDO::PARAM_STR) {
        switch (true) {
            case is_int($value):
                $type = \PDO::PARAM_INT;
                break;
            case is_bool($value):
                $type = \PDO::PARAM_BOOL;
                break;
            case is_null($value):
                $type = \PDO::PARAM_NULL;
                break;
            default:
                if(empty($type)) {
                    $type = \PDO::PARAM_STR;
                }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * @param array $params
     * @return bool
     */
    public function execute(array $params = []) {
        if (empty($params)) {
            return $this->stmt->execute();
        } else {
            return $this->stmt->execute($params);
        }
    }

    /**
     * returns the number of rows affected by the last DELETE, INSERT, or UPDATE
     * @return int
     */
    public function countRow() {
        return $this->stmt->rowCount();
    }

    /**
     * @param int $fetchStyle
     * @return array
     */
    public function fetch($fetchStyle = \PDO::FETCH_ASSOC) {
        return $this->stmt->fetchAll($fetchStyle);
    }

    /**
     * @param $table
     * @param $where
     * @param array $bind
     * @return int
     */
    public function delete($table, $where, array $bind = []) {
        $sql = 'DELETE FROM '.$table.' WHERE '.$where.';';

        return $this->run($sql, $bind);
    }


    /**
     * @param string $table
     * @return array
     * @throws DBException
     */
    private function getFullFields($table) {
        if(isset($this->tableToFields[$table])) {
            return $this->tableToFields[$table];
        }
        $driver = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $fields = [];

        if ($driver == 'sqlite') {
            $sql = "PRAGMA table_info('".$table."');";
            $key = "name";
        } elseif ($driver == 'mysql') {
            $sql = "DESCRIBE ".$table.";";
            $key = "Field";
        } else {
            $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '".$table."';";
            $key = "column_name";
        }

        if (false !== ($list = $this->run($sql))) {
            foreach ($list as $record) {
                $fields[] = $record[$key];
            }
        }
        $this->tableToFields[$table] = $fields;
        return $fields;
    }

    /**
     * @param string $table
     * @param array $info
     * @return array
     * @throws DBException
     */
    private function filter($table, array $info) {
        $fields = $this->getFullFields($table);
        $filterData = [];
        foreach($fields as $field) {
            if(array_key_exists($field, $info)) {
                $filterData[$field] = $info[$field];
            }
        }
        return $filterData;
    }

    /**
     * @param array $bind
     * @return array
     */
    private function cleanup($bind) {
        if(empty($bind)){
            $bind = [];
        }
        if(!is_array($bind)) {
            $bind = [$bind];
        }
        return $bind;
    }

    /**
     * @param string $table
     * @param array $info
     * @param bool $filterFields
     * @return int
     * @throws DBException
     */
    public function insert($table, array $info=[], $filterFields = true) {
        if($filterFields) {
            $fields = array_keys($this->filter($table, $info));
        } else {
            $fields = array_keys($info);
        }

        $sql = 'INSERT INTO `'.$table.'` (`'.implode('`, `', $fields).'`) VALUES (:'.implode(', :', $fields).');';

        $bind = [];
        foreach ($fields as $field) {
            $bind[":$field"] = $info[$field];
        }

        return $this->run($sql, $bind);
    }

    /**
     * @param string $table
     * @param array $recordList
     * @param int $partRowCount
     * @param string $onDuplicateKeyUpdate
     * @return int
     * @throws DBException
     */
    public function insertDataSet($table, array $recordList, $partRowCount=77, $onDuplicateKeyUpdate='') {
        $allRecordCount = count($recordList);
        $result = 0;
        if($allRecordCount) {
            $fields = array_keys($this->filter($table, Utils::getArraySecondLevelKeys($recordList)));
            $fieldsCount = count($fields);
            $bindRowStringTemplate = '('.implode(',', array_fill(0, $fieldsCount, '?')).')';
            $sqlTemplate = 'INSERT INTO '.$table.' (`'.implode('`, `', $fields).'`) VALUES ';
            $start = 0;
            $this->beginTransaction();
            do {
                $currentInsetRowCount = $allRecordCount;
                if($allRecordCount > $partRowCount) {
                    if($allRecordCount < 2*$partRowCount) {
                        $currentInsetRowCount = intval($allRecordCount/2);
                    } else {
                        $currentInsetRowCount = $partRowCount;
                    }
                }
                $bind = [];
                $end = $start + $currentInsetRowCount;
                $bindString = implode(', ', array_fill(0, $currentInsetRowCount, $bindRowStringTemplate));
                for($i=$start; $i<$end; ++$i) {
                    foreach($fields as $field) {
                        $bind[] = $recordList[$i][$field];
                    }
                }
                $sql = $sqlTemplate.$bindString;
                if(!empty($onDuplicateKeyUpdate)) {
                    $sql .= ' ON DUPLICATE KEY UPDATE '.$onDuplicateKeyUpdate;
                }
                $sql .= ';';
                try {
                    $result += $this->run($sql, $bind);
                } catch (DBException $e) {
                    $this->rollback();
                    throw $e;
                }
                $start += $currentInsetRowCount;
                $allRecordCount -= $currentInsetRowCount;
            } while($allRecordCount);
            $this->commit();
        }
        return $result;
    }

    /**
     * @param string $table
     * @param array $recordList
     * @param array $updateFields
     * @param int $partRowCount
     * @return int
     * @throws DBException
     */
    public function insertOrUpdateDataSet($table, array $recordList, array $updateFields, $partRowCount=77) {
        $onDuplicateKeyUpdate = [];
        foreach($updateFields as $field) {
            $onDuplicateKeyUpdate[] = $field.' = VALUES('.$field.')';
        }
        return $this->insertDataSet($table, $recordList, $partRowCount, implode(', ', $onDuplicateKeyUpdate));
    }

    /**
     * @param string $sql
     * @param array $bind
     * @param array $returnParams
     * @return array|int
     * @throws DBException
     */
    public function run($sql, $bind = [], $returnParams = []) {
        $sql = trim($sql);
        $bind = $this->cleanup($bind);
        $fetch = (isset($returnParams['fetch'])) ? $returnParams['fetch'] : false;

        try {
            $this->prepare($sql);
            if ($this->execute($bind) !== false) {
                if ($fetch || preg_match("/^(".implode("|", ["select", "describe", "pragma"]).") /i", $sql)){
                    return $this->fetch(\PDO::FETCH_ASSOC);
                } elseif (preg_match("/^(".implode("|", ["delete", "insert", "update"]).") /i", $sql)) {
                    return $this->countRow();
                } else {
                    return 0;
                }
            } else {
                throw new DBException('execute failed :(');
            }
        } catch (\PDOException $e) {
            throw new DBException($e);
        }
    }

    /**
     * @param string $table
     * @param string $where
     * @param array $bind
     * @param string $fields
     * @param string $orderBy
     * @param string $groupBy
     * @param string $limit
     * @param bool $calcFoundRows
     * @return array
     * @throws DBException
     */
    public function select($table, $where="", $bind=[], $fields="*", $orderBy="", $groupBy="", $limit="", $calcFoundRows = false) {
        if(is_array($fields)) {
            $fields = implode(', ', $fields);
        }
        $sql = "SELECT ";
        if ($calcFoundRows)
            $sql .= " SQL_CALC_FOUND_ROWS ";

        $sql .= $fields." FROM ".$table;

        if (!empty($where))
            $sql .= " WHERE ".$where;

        if (!empty($groupBy))
            $sql .= " GROUP BY ".$groupBy;

        if (!empty($orderBy))
            $sql .= " ORDER BY ".$orderBy;

        if (!empty($limit))
            $sql .= " LIMIT ".$limit;

        $sql .= ";";
        //var_dump($sql);

        return $this->run($sql, $bind, ['fetch' => true]);
    }
    public function foundRows() {
        $res = $this->run('SELECT FOUND_ROWS();', [], ['fetch' => true]);
        $count = 0;
        if(count($res) == 1) {
            foreach($res[0] as $v) {
                $count = $v;
            }
        }
        return $count;
    }





    /**
     * @param string $table
     * @param array $info
     * @param string $where
     * @param array $bind
     * @return int
     * @throws DBException
     */
    public function update($table, array $info, $where, $bind = []) {
        $fields = array_keys($this->filter($table, $info));

        $bind = $this->cleanup($bind);

        $sql = "UPDATE ".$table." SET ";

        foreach($fields as $field) {
            $fieldName = ":update_".$field;

            $bind[$fieldName] = $info[$field];
            $sql .= "`".$field."` = ".$fieldName.",";
        }

        $sql = substr($sql, 0, -1);
        $sql .= " WHERE ".$where.";";
        return boolval($this->run($sql, $bind));
    }


    /**
     * @return int
     */
    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }

    public function beginTransaction() {
        if($this->transactionNestingLevel == 0) {
            if(!$this->db->inTransaction()) {
                if($this->stmt) {
                    $this->stmt->closeCursor();
                }
                $this->db->beginTransaction();
            }
        }
        $this->transactionNestingLevel++;
    }

    public function commit() {
        $this->transactionNestingLevel--;
        if ($this->transactionNestingLevel == 0) {
            if($this->db->inTransaction()) {
                $this->db->commit();
            }
        }
    }

    public function rollback() {
        $this->transactionNestingLevel--;
        if ($this->transactionNestingLevel == 0) {
            if($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }

}
