<?php
namespace app\redis;

use \Redis;
use \Exception;
use \RedisException;

/**
 * redis底层组件类库
 * Class RedisBase
 * @package app\redis
 */
class RedisBase
{

    private static $handler;       //redis操作句柄
    protected $key;         //redis操作key

    public function __construct($key, $host, $port, $timeout = RedisConfig::REDIS_CONNECT_TIME_OUT, $reTryTimeout = RedisConfig::REDIS_CONNECT_RE_TRY_TIMEOUT)
    {
        if (!self::$handler && !(self::$handler instanceof Redis)) {
            self::$handler = new Redis();
            try {
                self::$handler->connect($host, $port, $timeout, $reTryTimeout);
            } catch (RedisException $rex) {
                throw new RException();
            } catch (Exception $ex) {
                throw new Exception();
            }
        }
        $this->key = $key;
    }

    public function ttl()
    {
        return self::$handler->ttl($this->key);
    }

    public function isExists()
    {
        return self::$handler->exists($this->key);
    }

    public function del()
    {
        return self::$handler->del($this->key);
    }

    public function expireAt($time, $set = RedisConfig::TIME_TYPE_SEC)
    {
        $ret = null;
        if ($set) {
            $ret = self::$handler->pExpireAt($this->key, $time);
        } else {
            $ret = self::$handler->expireAt($this->key, $time);
        }
        return $ret;
    }

    public function expire($time, $set = RedisConfig::TIME_TYPE_SEC)
    {
        $ret = null;
        if ($set) {
            $ret = self::$handler->pExpire($this->key, $time);
        } else {
            $ret = self::$handler->expire($this->key, $time);
        }
        return $ret;
    }

    public function sort($options)
    {
        return self::$handler->sort($this->key, $options);
    }

    public function type()
    {
        return self::$handler->type($this->key);
    }

    public function rename($newName)
    {
        return self::$handler->rename($this->key, $newName);
    }

    public function renameNx($newName)
    {
        return self::$handler->renameNx($this->key, $newName);
    }

    public static function sour()
    {
        return self::$handler;
    }

}