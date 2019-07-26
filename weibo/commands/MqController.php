<?php


namespace app\commands;


use app\common\SlowMqHandler;

/**
 * 队列消费脚本
 * Class MqController
 * @package app\commands
 */
class MqController extends CController
{

    /**
     * 用于redis队列的消息消费方法用
     */
    public function actionWbPush()
    {
        SlowMqHandler::consumeMq(1);
    }

}