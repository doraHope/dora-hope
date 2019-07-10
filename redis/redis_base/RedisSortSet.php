<?php


namespace app\redis;

use app\ParamsUtil;

class RedisSortSet extends RedisBase
{

    public function add($item, $score)
    {
        return self::sour()->zAdd($this->key, $score, $item);
    }

    public function rem($items)
    {
        $command = ParamsUtil::getEvalWithSingleArgs('self::sour()->zRem', array_merge([$this->key], $items));
        $ret = eval($command);
        return $ret;
    }

    public function rank($item)
    {
        return self::sour()->zRank($this->key, $item);
    }

    public function score($item)
    {
        return self::sour()->zScore($this->key, $item);
    }

    public function revRank($item)
    {
        return self::sour()->zRevRank($this->key, $item);
    }

    public function length()
    {
        return self::sour()->zCard($this->key);
    }

    public function count($min, $max)
    {
        return self::sour()->zCount($this->key, $min, $max);
    }

    public function incr($item, $append)
    {
        return self::sour()->zIncrBy($this->key, $append, $item);
    }

    public function range($start, $end, $withScore = false)
    {
        return self::sour()->zRange($this->key, $start, $end, $withScore);
    }

    public function rangeByScore($min, $max, $limit = [], $withScore = false)
    {
        if($limit) {
            return self::sour()->zRangeByScore($this-key(), $min, $max, [
                'limit' => $limit,
                'withscores' => $withScore
            ]);
        } else {
            return self::sour()->zRangeByScore($this->key, $min, $max, [
                'withscores' => $withScore
            ]);
        }
    }

    public function rangeByLex($min, $max, $offset, $length)
    {
        return self::sour()->zRangeByLex($this->key, $min, $max, $offset, $length);
    }

    public function inter($keys, $dest, $weight, $operation = RedisConfig::DEFAULT_ZINTER_OPERATION)
    {
        if($dest) {
            if($weight) {
                self::sour()->zInterstore($dest, array_merge([$this->key], $keys), $weight, $operation);
            } else {
                $weight = ParamsUtil::getXArray(1, count($keys)+1, RedisConfig::DATA_CHANGE_TYPE_COPY);
                self::sour()->zInterstore($dest, array_merge([$this->key], $keys), $weight, $operation);
            }
            return self::sour()->zRange($dest, 0, -1);
        }
        return [];
    }

    public function union($keys, $dest, $weight, $operation = RedisConfig::DEFAULT_ZINTER_OPERATION)
    {
        if($dest) {
            if($weight) {
                self::sour()->zunionstore($dest, array_merge([$this->key], $keys), $weight, $operation);
            } else {
                $weight = ParamsUtil::getXArray(1, count($keys)+1, RedisConfig::DATA_CHANGE_TYPE_COPY);
                self::sour()->zunionstore($dest, array_merge([$this->key], $keys), $weight, $operation);
            }
            return self::sour()->zRange($dest, 0, -1);
        }
        return [];
    }


}