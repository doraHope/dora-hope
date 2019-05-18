<?php

namespace app\models\base;

use yii\base\Component;

class Mysql extends Component
{
    protected $table = '';
    protected $host;
    protected $port;
    protected $user;
    protected $password;
    protected $dbName;
    protected $params;
    protected $SQL;

    const MYSQL_INT = 0;
    const MYSQL_STRING = 1;
    const MYSQL_AI = 2;
    const MYSQL_AS = 3;

    public static $con;

    public function __construct()
    {
        $this->params = [];
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'host':
                $this->setHost($value);
                break;
            case 'port':
                $this->setPort($value);
                break;
            case 'user':
                $this->setUser($value);
                break;
            case 'password':
                $this->setPassword($value);
                break;
            case 'db':
                $this->setDbName($value);
                break;
        }
    }

    public function setHost($value)
    {
        $this->host = $value;
    }

    public function setPort($value)
    {
        $this->port = $value;
    }

    public function setUser($value)
    {
        $this->user = $value;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function setDbName($value)
    {
        $this->dbName = $value;
    }


    protected function connect()
    {
        if (!self::$con instanceof \mysqli) {
            self::$con = new \mysqli($this->host.':'.$this->port, $this->user, $this->password, $this->dbName);
            if (self::$con->errno) {
                throw new \Exception(sprintf("%s", time()));
            }
        }
    }

    protected function db()
    {
        try {
            $this->connect();
            return self;
        } catch (MysqlException $e) {
            print('数据库加载出错');
            \Yii::$app->end();
        }
    }

    public function setParams($config)
    {
        if (isset($config['field'])) {
            $fields = implode(', ', $config['field']);
            $this->params['field'] = empty($fields) ? '*' : $fields;
        } else {
            $this->params['field'] = '*';
        }
        if (isset($config['where'])) {
            $whereConfig = [];
            foreach ($config['where'] as $_k => $item) {
                if ($item[0] === self::MYSQL_AI) {
                    $whereConfig[] = sprintf('%s in (%s)', implode(',', $item[1]));
                } else if ($item[0] === self::MYSQL_AS) {
                    $whereConfig[] = sprintf('%s in (\'%s\')', implode('\', \'', $item[1]));
                } else if ($item[0] === self::MYSQL_INT) {
                    $whereConfig[] = sprintf('%s = \'%s\'', $_k, $item[1]);
                } else if ($item[0] === self::MYSQL_STRING) {
                    $whereConfig[] = sprintf('%s = %d', $_k, intval($item[1]));
                }
            }
            $this->params['where'] = implode(' && ', $whereConfig);
        } else {
            $this->params['where'] = '1 = 1';
        }
        if (isset($config['order_by'])) {
            if (is_array($config['order_by']) && !empty($config['order_by'])) {
                $order_by = [];
                foreach ($config['order_by'] as $_k => $_v) {
                    $order_by[] = $_k . ' ' . $_v;
                }
                $this->params['order_by'] = 'order by ' . implode(', ', $order_by);
            } else {
                $this->params['order_by'] = '';
            }
        } else {
            $this->params['order_by'] = '';
        }
        if (isset($config['limit'])) {
            $this->params['limit'] = sprintf('limit %d, %d', intval($config['limit']['offset']), intval($config['limit']['length']));
        } else {
            $this->params['limit'] = '';
        }
    }

    /*----------------- 关于写的SQL操作*/
    /** 返回sql查询结果
     * @return array
     */
    protected function queryResult()
    {
        $result = self::$con->query($this->SQL);
        if(false === $result) {
            return false;
        }
        if (!$result) {
            //log
            return [];
        }
        $arrRet = [];
        while (!($row = $result->fetch_assoc())) {
            $arrRet[] = $row;
        }
        return $arrRet;
    }

    /** 构造$this->SQL
     * 由$this->params构造SQL
     */
    protected function createSQL()
    {
        $this->SQL = sprintf(
            "select %s from %s where %s %s %s",
            $this->params['field'],
            $this->table,
            $this->params['where'],
            $this->params['order_by'],
            $this->params['limit']
        );
    }

    /** 构造where条件查询字段
     * @param $where 条件查询变量
     * @return string
     */
    protected function createWhere($where)
    {
        $subWhere = [];
        foreach ($where as $_k => $item) {
            switch ($item[0]) {
                case self::MYSQL_INT:
                    $subWhere[] = sprintf('%s = \'%s\'', $_k, $item[1]);
                    break;
                case self::MYSQL_STRING:
                    $subWhere[] = sprintf('%s = %d', $_k, intval($item[1]));
                    break;
                case self::MYSQL_AI:
                    $subWhere[] = sprintf('%s in (%s)', implode(',', $item[1]));
                    break;
                case self::MYSQL_AS:
                    $subWhere[] = sprintf('%s in (\'%s\')', implode('\', \'', $item[1]));
                    break;
            }
        }
        return implode(" && ", $subWhere);
    }

    protected function execute()
    {
        if(false === $this->db()->query($this->SQL)) {
            return false;
        }
        return true;
    }


    public function queryByKey($type, $field, $value, $fields)
    {
//        try {
//            $this->connect();
//        } catch (MysqlException $e) {
//            print('数据库加载出错');
//            \Yii::$app->end();
//        }
        $queryCol = [];
        if(is_array($fields) && !empty($fields)) {
            foreach ($fields as $_k => $_v) {
                $queryCol[] = 'b.'.$_v;
            }
            $fields = implode(', ', $queryCol);
        } else {
            $fields = '*';
        }
        $SQL = $this->SQL;
        switch ($type) {
            case self::MYSQL_INT:
                $this->SQL = sprintf(
                    "select %s from (select %s from %s where %s = '%s') as a left join %s as b on a.%s = b.%s",
                    $fields,
                    field,
                    $this->table,
                    $field,
                    $value,
                    $this->table,
                    $field,
                    $field
                );
                break;
            case self::MYSQL_STRING:
                $this->SQL = sprintf(
                    "select %s from (select %s from %s where %s = %d) as a left join %s as b on a.%s = b.%s",
                    $fields,
                    field,
                    $this->table,
                    $field,
                    intval($value),
                    $this->table,
                    $field,
                    $field
                );
                break;
        }
        $ret = $this->queryResult();
        if(false === $ret) {
            $this->SQL = $SQL;
        }
        return $ret;
    }

    public function queryOneByKey($type, $field, $value)
    {
        $SQL = $this->SQL;
        switch ($type) {
            case self::MYSQL_INT:
                $this->SQL = sprintf(
                    "select b.* from (select %s from %s where %s = '%s') as a left join %s as b on a.%s = b.%s limit 0, 1",
                    field,
                    $this->table,
                    $field,
                    $value,
                    $this->table,
                    $field,
                    $field
                );
                break;
            case self::MYSQL_STRING:
                $this->SQL = sprintf(
                    "select b.* from (select %s from %s where %s = %d) as a left join %s as b on a.%s = b.%s limit 0, 1",
                    field,
                    $this->table,
                    $field,
                    intval($value),
                    $this->table,
                    $field,
                    $field
                );
                break;
        }

        $ret = $this->queryResult();
        if(false === $ret) {
            $this->SQL = $SQL;
        }
        return $ret;
    }

    public function queryOneByWhere($where, $field)
    {
        $where = $this->createWhere($where);
        $SQL = $this->SQL;
        $this->SQL = sprintf(
            "select b.* from (select %s from %s where %s) as a left join %s as b on a.%s = b.%s limit 0, 1",
            field,
            $this->table,
            $where,
            $this->table,
            $field,
            $field
        );
        $ret = $this->queryResult();
        if(false === $ret) {
            $this->SQL = $SQL;
        }
        return $ret;
    }

    public function queryListByWhere($where, $field, $offset, $length)
    {
        $where = $this->createWhere($where);
        $this->connect();
        $SQL = $this->SQL;
        $this->SQL = sprintf(
            "select b.* from (select %s from %s where %s) as a left join %s as b on a.%s = b.%s limit %d, %d",
            field,
            $this->table,
            $where,
            $this->table,
            $field,
            $field,
            intval($offset),
            intval($length)
        );

        $ret = $this->queryResult();
        if(false === $ret) {
            $this->SQL = $SQL;
        }
        return $ret;
    }

    public function queryWithLock()
    {
        $this->createSQL();
        $this->SQL .= ' for update';
        return $this->queryResult();
    }

    /*++++++++++++++++++++ 查询end*/

    /*-------------------- 写相关*/
    private function createField($field)
    {
        $arrField = [];
        foreach ($field as $_k => $item) {
            switch ($item[0]) {
                case self::MYSQL_INT:
                    $arrField[] = $_k.'='.$item[1];
                    break;
                case self::MYSQL_STRING:
                    $arrField[] = $_k.'=\''.$item[1].'\'';
            }
        }
        if(empty($arrField)) {
            return '1 = 1';
        } else {
            return implode(', ', $arrField);
        }
    }

    private function getKeyAndValue(&$data)
    {
        $keys = [];
        $values = [];
        foreach ($data as $_k => $item) {
            $keys[] = $_k;
            if(self::MYSQL_INT === $item[0]) {
                $values[] = intval($item[1]);
            } else {
                $values[] = '\''.$item[1].'\'';
            }
        }
        return [
            'key' => $keys,
            'value' => $values
        ];
    }

    public function update($where, $fields)
    {
        $SQL = $this->SQL;
        $where = $this->createWhere($where);
        $filed = $this->createField($fields);
        $this->SQL = sprintf('update %s set %s where %s', $this->table, $filed, $where);
        $ret = $this->execute();
        if(false === $ret) {
            $this->SQL = $SQL;
        }
        return $ret;
    }

    public function write($fields)
    {
        $arrInsert = $this->getKeyAndValue($fields);
        $strField = implode(', ', $arrInsert['key']);
        $strValue = implode(', ', $arrInsert['value']);
        $SQL = $this->SQL;
        $this->SQL = sprintf('insert into %s(%s) values (%s)', $this->table, $strField, $strValue);
        $ret = $this->execute();
        if(false === $ret) {
            $this->SQL = $SQL;
        }
        return $ret;
    }

    public function delete($where)
    {
        $SQL = $this->SQL;
        $where = $this->createWhere($where);
        $this->SQL = sprintf('delete from %s where %s', $this->table, $where);
        $ret = $this->execute();
        if(false === $ret) {
            $this->SQL = $SQL;
        }
        return $ret;
    }
}