<?php

namespace app\models\service;

use app\models\base\Redis;

class PreserveHash
{

    private $handler;

    public function __construct(Redis $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param $sourKey      用于维护权重的hash结构键名
     * @param $uid          用户内部唯一标识
     * @param $events       用户今日事件[发送微博\好友互动\获赞]
     */
    public function timerFreshProportion($sourKey, $uid, $events)
    {

    }




}