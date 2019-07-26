<?php
namespace app\example_3;

use app\redis\RedisHash;
use app\base\SimpleAlgorithm;
use app\redis\RedisSortSet;

/**
 * 基本计数器
 */
class Counter 
{

    private $eventId;               //事件id
    private $counter;               //关联到redis中的计数器

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
        $this->counter = new RedisHash(sprintf('counter:%s', $eventId));
    }

    /**
     * 信息量统计方法
     */
    public function count($interval)
    {
        echo 'type 1 => '.$interval.PHP_EOL;
        $key = SimpleAlgorithm::getTimeInterval(intval($interval));     //获取当前所处的时间精度
        echo 'type 2 => '.$key.PHP_EOL;
        $this->_append($interval);                                           //追加当前精度的计数器到计数器record中
        $this->counter->hIncr($key);                                    //计数器对应key的值增加1
    }

    /**
     * 追加当前计数器到计数器record中
     */
    private function _append($timestamp)
    {
        echo 'type 3 => 添加到record'.PHP_EOL;
        $key = sprintf('%s:%s', $this->eventId, $timestamp);
        echo 'type 4 => '.$key.PHP_EOL;
        $clean = new RedisSortSet(MAP_COUNTER_CLEAN[$this->eventId]);
        $clean->add($key, 0);
        echo 'type 5 => 添加完毕'.PHP_EOL;
    }

}