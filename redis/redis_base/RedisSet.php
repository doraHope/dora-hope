<?php


namespace app\redis;


use app\ParamsUtil;

class RedisSet extends RedisBase
{

    public function length()
    {
        return self::sour()->sCard($this->key);
    }

    public function add($items)
    {
        $command = ParamsUtil::getEvalWithSingleArgs('parent::sour()->sAdd', array_merge([$this->key], $items));
        $ret = eval($command);
        return $ret;

    }

    public function rem($items)
    {
        $command = ParamsUtil::getEvalWithSingleArgs('self::sour()->sRem', array_merge([$this->key], $items));
        $ret = eval($command);
        return $ret;
    }

    public function members()
    {
        return self::sour()->sMembers($this->key);
    }

    public function diff($set, $store = false, $dest = '')
    {
        if($store && $dest && is_string($dest)) {
            self::sour()->sDiffStore($dest, $this->key, $set);
            return self::sour()->sMembers($dest);

        } else {
            return self::sour()->sDiff($this->key, $set);
        }
    }

    public function inter($set, $store = false, $dest = '')
    {
        if($store && $dest && is_string($dest)) {
            self::sour()->sInterStore($dest, $this->key, $set);
            return self::sour()->sMembers($dest);

        } else {
            return self::sour()->sInter($this->key, $set);
        }
    }

    public function union($set, $store = false, $dest = '')
    {
        if($store && $dest && is_string($dest)) {
            self::sour()->sUnionStore($dest, $this->key, $set);
            return self::sour()->sMembers($dest);

        } else {
            return self::sour()->sUnion($this->key, $set);
        }
    }

}