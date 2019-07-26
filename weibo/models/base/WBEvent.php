<?php


namespace app\models\base;

use \WeiBoConfig;

/**
 * 微博事件类的抽象类，关于类从抽象到具体的设计并没有完成
 * Class WBEvent
 * @package app\models\base
 */
abstract class WBEvent
{
    public $event;
    public $queues;

    public function __construct($eventType)
    {
        $this->queues = WeiBoConfig::$EVENT_QUEUES[$eventType];
    }

    abstract function push($id, $information);
}