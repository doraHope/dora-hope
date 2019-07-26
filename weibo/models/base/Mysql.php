<?php

namespace app\models\base;

use yii\base\Component;

/**
 * 一个封装的不是特别好的Mysql基础服务类
 * Class Mysql
 * @package app\models\base
 */
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


    public function connect()
    {
        if (!self::$con instanceof \mysqli) {
            try{
                self::$con = new \mysqli($this->host.':'.$this->port, $this->user, $this->password, $this->dbName);
                if (self::$con->errno) {
                    throw new \Exception(sprintf("%s", time()));
                }
            } catch (\Exception $e) {
                throw new $e;
            }
        }
    }

    protected function db()
    {
        return self::$con;
    }

    /**
     * 有config构造SQL
     * @param $config   SQL构造参数
     */
    public function setParams($config)
    {
        //查询的字段
        if (isset($config['field'])) {
            $fields = implode(', ', $config['field']);
            $this->params['field'] = empty($fields) ? '*' : $fields;
        } else {
            $this->params['field'] = '*';
        }
        //查询的条件, 暂时支持 '=' 和 'in' 的匹配条件, 且防SQL注入攻击
        if (isset($config['where'])) {
            $whereConfig = [];
            foreach ($config['where'] as $_k => $item) {
                if ($item[0] === self::MYSQL_AI) {  //column in (1, 2, 3)
                    $whereConfig[] = sprintf('%s in (%s)', implode(',', $item[1]));
                } else if ($item[0] === self::MYSQL_AS) {   //column in ('a', 'b')
                    $whereConfig[] = sprintf('%s in (\'%s\')', implode('\', \'', $item[1]));
                } else if ($item[0] === self::MYSQL_INT) {  //column = 1
                    $whereConfig[] = sprintf('%s = \'%s\'', $_k, $item[1]);
                } else if ($item[0] === self::MYSQL_STRING) { //column = '1'
                    $whereConfig[] = sprintf('%s = %d', $_k, intval($item[1]));
                }
            }
            //拼接成where条件
            $this->params['where'] = implode(' && ', $whereConfig);
        } else {
            $this->params['where'] = '1 = 1';
        }
        //构造SQL order by条件
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
        //构造limit 条件
        if (isset($config['limit'])) {
            $this->params['limit'] = sprintf('limit %d, %d', intval($config['limit']['offset']), intval($config['limit']['length']));
        } else {
            $this->params['limit'] = '';
        }
    }

    /*----------------- 关于写的SQL操作*/
    /**
     * 执行构造完成的SQL，并返回结果
     * @return array
     */
    protected function queryResult()
    {
        $result = self::$con->query($this->SQL);    //SQL执行
        if(false === $result) {
            return false;
        }
        if (!$result) {
            //log
            return [];
        }
        $arrRet = [];
        while ($row = $result->fetch_assoc()) {     //遍历结果集合并存储
            $arrRet[] = $row;
        }
        return $arrRet;                             //返回查询结果
    }

    /** 构造$this->SQL
     * 由$this->params构造SQL
     */
    protected function createSQL()
    {
        //由成员变量params构造SQL
        $this->SQL = sprintf(
            "select %s from %s where %s %s %s",
            $this->params['field'],
            $this->table,
            $this->params['where'],
            $this->params['order_by'],
            $this->params['limit']
        );
    }

    /**
     * 构造where条件查询句子
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

    /**
     * 用于update, delete, insert，却别于update
     * @return bool
     */
    protected function execute()
    {
        if(false === $this->db()->query($this->SQL)) {
            return false;
        }
        return true;
    }

    /**
     * 获取刚插入记录的id值
     * @return mixed
     */
    public function getInsertId()
    {
        return Mysql::$con->insert_id;
    }

    /**
     * 按 column = value的方式，查询表中是否存在相关记录
     * @param $type         column字段类型
     * @param $field        column字段名称
     * @param $value        数值
     * @return array
     */
    public function queryByPkForExists($type, $field, $value)
    {
        $SQL = $this->SQL;
        switch ($type) {
            case self::MYSQL_STRING:
                $this->SQL = sprintf(
                    "select 1 from %s where %s = '%s' limit 0, 1",
                    $this->table,
                    $field,
                    $value
                );
                break;
            case self::MYSQL_INT:
                $this->SQL = sprintf(
                    "select 1 from %s where %s = %d limit 0, 1",
                    $this->table,
                    $field,
                    intval($value)
                );
                break;
        }

        $ret = $this->queryResult();
        if(false === $ret) {
            $this->SQL = $SQL;
        }
        return $ret;
    }

    /**
     * 按 column = value的方式，获取匹配的记录
     * @param $type         column字段类型
     * @param $field        column字段名称
     * @param $value        数值
     * @param $fields       查询的字段
     * @return array
     */
    public function queryByKey($type, $field, $value, $fields)
    {
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
            case self::MYSQL_STRING:
                $this->SQL = sprintf(
                    "select %s from  %s where %s = '%s'",
                    $fields,
                    $this->table,
                    $field,
                    $value
                );
                break;
            case self::MYSQL_INT:
                $this->SQL = sprintf(
                    "select %s from %s where %s = %d",
                    $fields,
                    $this->table,
                    $field,
                    intval($value)
                );
                break;
        }
        $ret = $this->queryResult();
        if(false === $ret) {
            $this->SQL = $SQL;
        }
        return $ret;
    }

    /**
     * 按 column = value的方式，只返回结果集中的一条
     * @param $type     column字段类型
     * @param $field    column字段名称
     * @param $value    数值
     * @return mixed
     */
    public function queryOneByKey($type, $field, $value)
    {
        $SQL = $this->SQL;
        switch ($type) {
            case self::MYSQL_STRING:
                $this->SQL = sprintf(
                    "select * from %s where %s = '%s' limit 0, 1",
                    $this->table,
                    $field,
                    $value
                );
                break;
            case self::MYSQL_INT:
                $this->SQL = sprintf(
                    "select * from %s where %s = %d limit 0, 1",
                    $this->table,
                    $field,
                    intval($value)
                );
                break;
        }

        $ret = $this->queryResult();
        if(false === $ret) {
            $this->SQL = $SQL;
        }
        return $ret[0];
    }

    /**
     * 连表查询，查询a的集合field 匹配 b的filed, 取出表b的匹配结果集合的第一条记录
     * @param $where    子查询条件的相关配置
     * @param $field    匹配字段
     * @return mixed
     */
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
        return $ret[0];
    }

    /**
     * 连表查询，查询a的集合field 匹配 b的filed, 取出表b的匹配结果集合
     * @param $where    子查询条件的相关配置
     * @param $field    匹配字段
     * @param $offset   结果集合偏移量
     * @param $length   结果集合取长
     * @return mixed
     */
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
        return $ret[0];
    }

    /**
     * 通过加排他锁[行锁}方式查询，同步方式查询，保证数据操作原子性
     * @return array
     */
    public function queryWithLock()
    {
        $this->createSQL();
        $this->SQL .= ' for update';
        return $this->queryResult();
    }

    /**
     * 可传入手工sql执行，并返回相关结果
     * @param $SQL
     * @return array
     */
    public function query($SQL)
    {
        $tmp = $this->SQL;
        $this->SQL = $SQL;
        $ret = $this->queryResult();
        if(false === $ret) {
            $this->SQL = $tmp;
        }
        return $ret;
    }

    /**
     * 获取表中key字段值的最大值+1
     * @param $key
     * @return array|int
     */
    public function queryPID($key)
    {
        $SQL = $this->SQL;
        $this->SQL = sprintf('select max(%s)+1 as id from %s for update', $key, $this->table);
        $ret = $this->queryResult();
        if(false === $ret) {
            $this->SQL = $SQL;
        }
        if(!$ret) {
            return [
                $key => 1
            ];
        }
        return intval($ret[0]['id']);
    }

    /*++++++++++++++++++++ 查询end*/

    /*-------------------- 写相关*/
    /**
     * 构成where匹配条件子句
     * @param $field
     * @return string
     */
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

    /**
     * 用于插入语句，获取关联数组的key集合和value集合并返回
     * @param $data
     * @return array
     */
    private function getKeyAndValue(&$data)
    {
        $keys = [];
        $values = [];
        foreach ($data as $_k => $item) {
            $keys[] = $_k;
            if(self::MYSQL_INT === $item[0]) {
                $values[] = intval($item[1]);
            } else {
                $values[] = '\''.addslashes($item[1]).'\'';
            }
        }
        return [
            'key' => $keys,
            'value' => $values
        ];
    }

    /**
     * 更新操作
     * @param $where        构成查询条件的关联数组
     * @param $fields       构成查询字段的关联数组
     * @return bool
     */
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

    /**
     * 写操作，
     * @param $fields       写入字段->值的关联数组
     * @return bool
     */
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

    /**
     * 按where子句条件删除匹配记录
     * @param $where        构成where子句的关联数组
     * @return bool
     */
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

    /*------------ 事务*/
    /**
     * 开始一个事务，即设置auto_commit  = 0
     */
    public function transaction()
    {
        self::$con->autocommit(false);
    }

    /**
     * 提交一个事务
     */
    public function commit()
    {
        self::$con->commit();
        self::$con->autocommit(true);
    }

    /**
     * 回滚一个事务
     */
    public function rollback()
    {
        self::$con->rollback();
        self::$con->autocommit(true);
    }
}