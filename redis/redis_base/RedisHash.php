<?php


namespace app\redis;

/**
 * Redis中hash类型key操作基本类
 * Class RedisHash
 * @package app\redis
 */
class RedisHash extends RedisBase
{

    public function hExists($h_key)
    {
        return parent::sour()->hExists($this->key, $h_key);
    }

    public function get($h_key)
    {
        return parent::sour()->hGet($this->key, $h_key);
    }

    public function gets($keys)
    {
        if ($keys && is_array($keys)) {
            return parent::sour()->hMGet($this->key, $keys);
        }
        return [];
    }

    public function getAll()
    {
        return parent::sour()->hGetAll($this->key);
    }

    public function set($h_key, $h_value)
    {
        return parent::sour()->set($this->key, $h_key, $h_value);
    }

    public function sets($params)
    {
        return parent::sour()->hMSet($this->key, $params);
    }

    public function hIncr($h_k, $value = RedisConfig::DEFAULT_DATA_INT)
    {
        if ($value) {
            return parent::sour()->hIncrBy($this->key, $h_k, $value);
        }
        return true;
    }

    public function hIncrByFloat($h_k, $value = RedisConfig::DEFAULT_DATA_INT)
    {
        if($value) {
            return parent::sour()->hIncrByFloat($this->key, $h_k, $value);
        }
        return true;
    }

    public function getKeys()
    {
        return self::sour()->hKeys($this->key);
    }

    public function getValues()
    {
        return self::sour()->hVals($this->key);
    }

    public function length()
    {
        return self::sour()->hLen($this->key);
    }

}