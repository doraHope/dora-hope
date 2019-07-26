<?php


namespace app\models\base;


use app\models\event\CommentEvent;
use app\models\event\LikeEvent;
use app\models\event\ReplyEvent;
use app\models\event\WeiBoEvent;
use app\models\exception\EventException;

/**
 * 基础Event事件基础类[具体事件(发送微博，点赞，评论转发等事件)的基类]，算是类多态的一种简单实现
 * Class Event
 * @package app\models\base
 */
class Event
{

    public $event;

    public function __construct($eventType)
    {
        switch ($eventType) {
            case WB_EVENT_WEI_BO:
                $this->event = new WeiBoEvent($eventType);
                break;
            case WB_EVENT_COMMENT:
                $this->event = new CommentEvent($eventType);
                break;
            case WB_EVENT_REPLY:
                $this->event = new ReplyEvent($eventType);
                break;
            case WB_EVENT_LIKE:
                $this->event = new LikeEvent($eventType);
                break;
        }
    }

    public function push($information)
    {
        if($this->event) {
            $this->event->push($information);
        } else {
            throw new EventException();
        }
    }


}