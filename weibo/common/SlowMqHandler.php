<?php


namespace app\common;

use Yii;
/**
 * 消息队列处理
 * @package app\common
 */
class SlowMqHandler
{

    const KEY = WB_MQ_PUSH_SLOW;
    const SALT = WB_MQ_PUSH_SLOW_LENGTH;

    /**
     * 将微博发送到粉丝空间列表中
     * @param $handler
     * @param $fid
     * @param $wbId
     */
    public static function pushFens(&$handler, $fid, $wbId)
    {
        $key = WB_FOCUS.$fid;
        $handler->listPush($key, $wbId);
    }

    /**
     * 消费消息
     * @param $handler
     * @param $uid
     * @param $wbId
     * @param $option
     */
    public static function messageConsume(&$handler, $uid, $wbId, $option)
    {
        $key = WB_FENS_ZET.$uid;
        $uList = $handler->zSetRangeByScore(
            $key,
            floatval($option['begin']),
            floatval($option['end']),
            [intval($option['limit']), intval($option['length'])]
        );
        foreach ($uList as $u) {
            self::pushFens($handler, $u, $wbId);
        }
    }

    /**
     * 消息队列
     * @param $index
     * @throws \yii\base\InvalidConfigException
     */
    public static function consumeMq($index)
    {
        $redis = Yii::$app->get('redis');
        $key = self::KEY.($index%self::SALT);
        while(true) {
            $message = $redis->listPop($key);
            if($message) {
                $arrMsg = json_decode($message, true);
                $messageOpt = $arrMsg['option'];
                $messageCon = $arrMsg['content'];
                self::messageConsume($redis, intval($messageCon[0]), intval($messageCon[1]), $messageOpt);
            }
            usleep(1000*10);            //10ms
        }
    }

}