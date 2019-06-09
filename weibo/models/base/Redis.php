<?php


namespace app\models\base;


use yii\base\Component;

class Redis extends Component
{

    protected $host;
    protected $port;
    protected $user;
    protected $password;
    protected $timeout;
    protected $retryInterval;
    protected $readTimeout;
    protected $pconnect;

    public static $db;

    /**
     * Redis constructor.
     */
    public function __construct()
    {

        self::$db = new \Redis();
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
            case 'pconnect':
                $this->setPconnect($value);
                break;
            case 'timeout':
                $this->setTimeout($value);
                break;
            case 'retryInterval':
                $this->setRetryInterval($value);
                break;
            case 'readTimeout':
                $this->setReadTimeout($value);
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

    public function setPconnect($value)
    {
        $this->pconnect = $value;
    }

    public function setTimeout($value)
    {
        $this->timeout = $value;
    }

    public function setRetryInterval($value)
    {
        $this->retryInterval = $value;
    }

    public function setReadTimeout($value)
    {
        $this->readTimeout = $value;
    }


    /** redis连接
     * @throws \Exception
     */
    public function connect()
    {
        if ($this->pconnect) {
            //长连接
            try {
                Redis::$db->pconnect($this->host, $this->port, $this->timeout, $this->retryInterval, $this->readTimeout);
            } catch (\RedisException $e) {
                throw new \Exception($e->getMessage());
            }
        } else {
            //短链接
            try {
                Redis::$db->connect($this->host, $this->port, $this->timeout, $this->retryInterval, $this->readTimeout);
            } catch (\RedisException $e) {
                throw new \Exception('redis连接失败');
            }
        }
    }

    /** 返回本实例
     * @return $this
     * @throws \yii\base\ExitException
     */
    public function db()
    {
        try {
            $this->connect();
            return $this;
        } catch (\Exception $e) {
            //log
            \Yii::$app->end();
        }
    }

    /*------------------- string操作*/
    /** 设置string 键值
     * @param $key      键名
     * @param $value    键值
     * @param int $expired
     * @return mixed
     */
    public function stringSet($key, $value, $expired = 0)
    {
        return Redis::$db->set($key, $value, $expired);
    }

    /** 获取键值
     * @param $key 键名
     * @return bool|string
     */
    public function stringGet($key)
    {
        return Redis::$db->get($key);
    }

    /*------------------- 列表操作规定: 左进右出*/

    /** 推入列表
     * @param $key      键名
     * @param $value    添加元素
     */
    public function listPush($key, $value)
    {
        Redis::$db->lPush($key, $value);
    }

    /**
     * @param $key
     * @param int $timeout
     * @return array
     */
    public function listBPop($key, $timeout = 0)
    {
        return Redis::$db->blPop($key, $timeout);
    }

    /** 推出列表
     * @param $key      键名
     * @return mixed
     */
    public function listPop($key)
    {
        return Redis::$db->rPop($key);
    }

    /** 从列表中获取一段范围的元素集合
     * @param $key      键名
     * @param $offset   偏移量
     * @param $length   长度
     * @return array
     */
    public function listRange($key, $offset, $length)
    {
        $start = $offset * 10;
        $end = $start + $length;
        $data = Redis::$db->lRange($key, $start, $end);
        if (false !== $data) {
            return $data;
        }
        //log
        return [];
    }

    /** 列表裁剪，将列表裁剪保留范围内的
     * @param $key      键名
     * @param $offset   偏移量
     * @param $length   长度
     * @return array
     */
    public function listTrim($key, $offset, $length)
    {
        $start = $offset;
        $end = $start + $length;
        return Redis::$db->lTrim($key, $start, $end);
    }

    /** 移除列表中的某个元素
     * @param $key      键名
     * @param $row      元素
     */
    public function listRem($key, $row)
    {
        Redis::$db->lRem($key, $row);
    }

    /*------------------- 集合操作*/
    /** 批量添加元素到集合
     * @param $key          键名
     * @param $items        元素
     * @return array
     */
    public function setMAdd($key, $items)
    {
        $ret = [];
        if (is_array($items)) {
            foreach ($items as $_k => $item) {
                $ret[$_k] = Redis::$db->sAdd($key, $item);
            }
        }
        return $ret;
    }

    /** 添加一个元素到集合
     * @param $key      键名
     * @param $item     元素
     * @return int
     */
    public function setAdd($key, $item)
    {
        return Redis::$db->sAdd($key, $item);
    }

    /** 移除集合中的某个元素
     * @param $key      键名
     * @param $item     元素
     */
    public function setRem($key, $item)
    {
        Redis::$db->sRem($key, $item);
    }

    /** 集合长度
     * @param $key      键名
     * @return int
     */
    public function setSize($key)
    {
        return Redis::$db->sCard($key);
    }

    /** 去两个集合的差集到dstKey
     * @param $dstKey       目标集合
     * @param $sourKeyA     源集合A
     * @param $sourKeyB     源集合B
     * @return int
     */
    public function setDiffStore($dstKey, $sourKeyA, $sourKeyB)
    {
        return Redis::$db->sDiffStore($dstKey, $sourKeyA, $sourKeyB);
    }

    /*------------------- 有序集合操作*/
    /** 有序集合批量添加
     * @param $key      键名
     * @param $items    添加元素
     */
    public function zSetMAdd($key, $items)
    {
        foreach ($items as $_k => $item) {
            Redis::$db->zAdd($key, $item, $_k);
        }
    }

    /** 有序集合单个元素添加
     * @param $key      键名
     * @param $item     元素
     * @return int
     */
    public function zSetSet($key, $item)
    {
        return Redis::$db->zAdd($key, $item['score'], $item['key']);
    }

    /** 有序集合长度
     * @param $key      键名
     */
    public function zSetSize($key)
    {
        return Redis::$db->zSize($key);
    }

    /** 增加有序集合的单个元素的score
     * @param $key      键名
     * @param $item     元素
     * @param $score    增加分数
     * @return float
     */
    public function zSetIncrBy($key, $item, $score)
    {
        return Redis::$db->zIncrBy($key, $item, $score);
    }

    /** 由score获取有序集合范围内的元素集合
     * @param $key              键名
     * @param $min              最小值
     * @param $max              最大值
     * @param array $limit 偏移量和长度
     * @param bool $withScore 是否取出score
     * @return array
     */
    public function zSetRangeByScore($key, $min, $max, $limit = [], $withScore = false)
    {
        if (empty($limit) && !$withScore) {
            $items = Redis::$db->zRangeByScore($key, $min, $max);
        } elseif (!empty($limit)) {
            $items = Redis::$db->zRangeByScore($key, $min, $max, ['limit' => $limit]);
        } elseif ($withScore) {
            $items = Redis::$db->zRangeByScore($key, $min, $max, ['withscores' => $withScore]);
        } else {
            $items = Redis::$db->zRangeByScore($key, $min, $max, ['withscores' => $withScore, 'limit' => $limit]);
        }
        return $items;
    }

    /** 获取集合中score最大和最小值
     * @param $items           有序集合
     * @return array
     */
    private function getMaxAndMin($items)
    {
        $max = PHP_INT_MIN;
        $min = PHP_INT_MAX;
        foreach ($items as $_k => $item) {
            if ($item < $min) {
                $min = $item;
            }
            if ($item > $max) {
                $max = $item;
            }
        }
        return [$min, $max];
    }

    public function zGetScoreByRange($key, $index)
    {
        $number = Redis::$db->sCard($key);
    }

    /** 获取有序集合的最大值和最小值
     * @param $key          键名
     * @return array
     */
    public function zSetGetMaxAndMin($key)
    {
        $number = Redis::$db->zSize($key);
        if ($number > 0) {
            $items = Redis::$db->zRange($key, 0, $number, true);                //这里可能存在内存溢出，暂不考虑
            return $this->getMaxAndMin($items);
        }
        return [];
    }

    /** 由添加元素序号获取范围内的元素集合
     * @param $key          键名
     * @param $start        开始位置
     * @param $end          结束位置
     * @param bool $withScore 是否也取出score
     * @return array
     */
    public function zSetByRange($key, $start, $end, $withScore = false)
    {
        return Redis::$db->zRange($key, $start, $end, $withScore);
    }

    /*--------------------- hash操作*/
    /** 向hash中插入关联数组
     * @param $hashKey      hash键名
     * @param $items        关联数组
     */
    public function hashMSet($hashKey, $items)
    {
        Redis::$db->hMSet($hashKey, $items);
    }

    /** 从hash中获取$keys中的关联数组
     * @param $hashKey          hash键名
     * @param $keys             键名数组
     * @return string
     */
    public function hashMGet($hashKey, $keys)
    {
        return Redis::$db->hGet($hashKey, $keys);
    }

    /** 向hash中插入一条关联数组
     * @param $hashKey          hash键名
     * @param $key              插入hash字段
     * @param $value            键入hash键值
     * @return bool|int
     */
    public function hashSet($hashKey, $key, $value)
    {
        return Redis::$db->hSet($hashKey, $key, $value);
    }

    /** 如其名
     * @param $hashKey          hash键名
     * @param $key              插入hash字段
     * @param $intValue         增量值
     * @return bool
     */
    public function hashIncr($hashKey, $key, $intValue)
    {
        try{
            Redis::$db->hIncrBy($hashKey, $key, intval($intValue));
            return true;
        } catch (\RedisException $ex) {
            return false;
        }
    }

    /** 从hash中获取指定字段的值
     * @param $hashKey          hash键名
     * @param $key              插入hash字段
     * @return string
     */
    public function hashGet($hashKey, $key)
    {
        return Redis::$db->hGet($hashKey, $key);
    }

    /** 从hash中删除指定字段
     * @param $hashKey          hash键名
     * @param $key              获取的hash字段
     */
    public function hashRem($hashKey, $key)
    {
        Redis::$db->hDel($hashKey, $key);
    }

    /*--------------------- key操作相关*/
    public function delKey($key)
    {
        Redis::$db->delete($key);
    }

    public function expireKey($key, $expired)
    {
        Redis::$db->set($key, $expired);
    }


}