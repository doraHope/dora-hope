<?php


namespace app\common;

use Yii;

/**
 * 微博消息redis存储
 * @package app\common
 */
class WBRedisHandler
{

    /**
     * 获取用户粉丝数量
     * @param $uid
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public static function getFensCount($uid)
    {
        $redis = Yii::$app->get('redis');
        $key = WB_FENS . $uid;
        return $redis->zSetSize($key);
    }


    /**
     * 获取用户的粉丝列表
     * @param $uid
     * @param $offset
     * @param $length
     * @param $withScore
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public static function getFensZByRange($uid, $offset, $length, $withScore = false)
    {
        $redis = Yii::$app->get('redis');
        $key = WB_FENS_ZET . $uid;
        return $redis->zSetByRange($key, $offset, $length, $withScore);
    }


    public static function getFensZByScore($uid, $min, $max, $withScore = false)
    {
        $redis = Yii::$app->get('redis');
        $key = WB_FENS_ZET . $uid;
        return $redis->zSetRangeByScore($key, $min, $max);
    }

    private static function _pushWeiBoSub(&$handler, $keyMq, $message)
    {
        $handler->listPush($keyMq, $message);
    }


    /**
     * 注: 并不需要强制所有粉丝都能收到
     * 用户发送微博的时候，将用户的消息发送至消息队列，后台利用脚本将消息push到粉丝的微博朋友圈中
     * @param $uid
     * @param $wbId
     * @throws \yii\base\InvalidConfigException
     */
    public static function pushWeiBo($uid, $wbId)
    {
        $redis = Yii::$app->get('redis');
        $zKey = WB_FENS_ZET . $uid;
        $ret = $redis->zSetGetMaxAndMin($zKey);
        if (!$ret) {
            return;
        }
        $min = $ret[0];         //粉丝中积分最低者
        $max = $ret[1];         //粉丝中积分最高者
        $key = WB_FENS . $uid;
        $intFensCount = $redis->setSize($key);
        if ($intFensCount > QUICK_SLOW_LIMIT_LIEN) {
            $keyMq = WB_MQ_PUSH_QUICK . ($uid % WB_MQ_PUSH_QUICK_LENGTH);
        } else {
            $keyMq = WB_MQ_PUSH_SLOW . ($uid % WB_MQ_PUSH_SLOW_LENGTH);
        }
        $index = 0;
        while ($index < $intFensCount) {
            $message = json_encode([
                'option' => [
                    'begin' => $min,
                    'end' => $max,
                    'limit' => $index,
                    'length' => WB_PUSH_LENGTH
                ],
                'content' => [
                    $uid, $wbId
                ]
            ]);
            self::_pushWeiBoSub($redis, $keyMq, $message);
            $index += WB_PUSH_LENGTH;
        }
    }

    public static function push2selfWB($uid, $wbId)
    {
        $redis = Yii::$app->get('redis');
        $key = WB_SELF.$uid;
        $message = json_encode([
            'timestamp' => time(),
            'content' => $wbId
        ]);
        self::_pushWeiBoSub($redis, $key, $message);
    }
}