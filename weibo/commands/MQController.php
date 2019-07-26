<?php


namespace app\commands;


use app\common\SlowMqHandler;

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