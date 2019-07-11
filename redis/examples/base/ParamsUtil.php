<?php
namespace app;

use app\redis\RedisConfig;
use app\redis\RException;

class ParamsUtil
{

    public static function getEvalWithSingleArgs($func, $args)
    {
        $ret = '';
        if($args && is_array($args)) {
            $ret = $func.'(\''.implode('\', \'', $args).'\');';
        } else if($args) {
            $ret = $func.'(\''.implode('\', \'', $args).'\');';
        }
        return $ret;
    }

    public static function getXArray($value, $length, $type)
    {
        $ret = [];
        switch ($type) {
            case RedisConfig::DATA_CHANGE_TYPE_COPY :
                for ($i = 0; $i < $length; $i++) {
                    $ret[] = $value;
                }
                break;
            case RedisConfig::DATA_CHANGE_TYPE_INCR:
                for ($i = 0; $i < $length; $i++) {
                    $ret[] = $value++;
                }
                break;
            case RedisConfig::DATA_CHANGE_TYPE_DECR:
                for ($i = 0; $i < $length; $i++) {
                    $ret[] = $value--;
                }
                break;
        }
        return $ret;
    }

}