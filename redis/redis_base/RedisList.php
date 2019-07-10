<?php


namespace app\redis;

use \Redis;
/**
 * Redis中list类型key操作基本类
 * Class RedisHash
 * @package app\redis
 */
class RedisList extends RedisBase
{

    public function indexOf($index)
    {
        return self::sour()->lIndex($this->key, $index);
    }

    public function lPush($item)
    {
        return self::sour()->lPush($this->key, $item);
    }

    public function lPop()
    {
        return self::sour()->lPop($this->key);
    }

    public function rPush($item)
    {
        return self::sour()->rPush($this->key, $item);
    }

    public function rPop()
    {
        return self::sour()->rPop($this->key);
    }

    public function range($start, $end)
    {
        return self::sour()->lRange($this->key, $start, $end);
    }

    public function rem($item, $count = RedisConfig::DEFAULT_DATA_INT)
    {
        return self::sour()->lRem($this->key, $item, $count);
    }

    public function change($index, $item)
    {
        return self::sour()->lSet($this->key, $index, $item);
    }

    public function insert($firstItem, $item, $pos = Redis::BEFORE)
    {
        return self::sour()->lInsert($this->key, $pos, $firstItem, $item);
    }

    public function trim($start, $end)
    {
        return self::sour()->listTrim($this->key, $start, $end);
    }
}