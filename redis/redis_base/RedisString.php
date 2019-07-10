<?php

namespace app\redis;

/**
 * Redis中string类型key操作基本类
 * Class RedisString
 * @package app\redis
 */
class RedisString extends RedisBase
{

    public function get()
    {
        return parent::sour()->get($this->key);
    }

    public function set($value, $expire = RedisConfig::DEFAULT_EXPIRE_TIME, $type = RedisConfig::TIME_TYPE_SEC)
    {
        if($type) {
            return parent::sour()->set($this->key, $value, ['xx', 'px' => $expire]);
        } else {
            return parent::sour()->set($this->key, $value, ['xx', 'ex' => $expire]);
        }
    }

    public function setNx($value, $expire = RedisConfig::DEFAULT_EXPIRE_TIME, $type = RedisConfig::TIME_TYPE_SEC)
    {
        if($type) {
            return parent::sour()->set($this->key, $value, ['nx', 'px' => $expire]);
        } else {
            return parent::sour()->set($this->key, $value, ['nx', 'ex' => $expire]);
        }
    }

    public function incr($value = RedisConfig::DEFAULT_DATA_INT)
    {
        if($value) {
            return parent::sour()->incrBy($this->key, $value);
        } else {
            return parent::sour()->incr($this->key);
        }
    }

    public function decr($value = RedisConfig::DEFAULT_DATA_INT)
    {
        if($value) {
            return parent::sour()->decrBy($this->key, -abs($value));
        } else {
            return parent::sour()->decr($this->key);
        }
    }

    public function incrByFloat($value = RedisConfig::DEFAULT_DATA_INT) {
        if($value) {
            return parent::sour()->incrByFloat($this->key, $value);
        }
    }

    public function subString($offset, $length)
    {
        return parent::sour()->getRange($this->key, $offset, $length);
    }

    public function appendString($offset, $value)
    {
        return parent::sour()->setRange($this->key, $offset, $value);
    }

    public function length()
    {
        return parent::sour()->strlen($this->key);
    }
}

