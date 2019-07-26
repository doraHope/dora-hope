<?php


namespace app\common;

use Yii;

/**
 * 微博消息处理公共类
 * @package app\common
 */
class WBMessageCommon
{

    /**
     * 微博消息校验+矫正
     */
    public static function MSCorrect(& $objWB)
    {
        $objWB['content'] = trim($objWB['content']);
        if (!isset($objWB['content']) || !$objWB['content']) {
            return false;
        }
        $objWB['content'] = addslashes($objWB['content']);
        if (isset($objWB['extra_type']) && ($objWB['extra_type'] = intval($objWB['extra_type'])) === TYPE_FILE) {
            if (!$_FILES['extra_file']) {
                $objWB['extra_type'] = TYPE_TEXT;
            } else {
                $objWB['files'] = [];
                foreach ($_FILES['extra_file'] as $file) {
                    $objWB['files'] = $file;
                }
            }
        } else {
            $objWB['extra_type'] = '';
        }
        return true;
    }

    /**
     * 从微博内容get出要@的微博用户集合，用于后台将yo
     * @param $message
     * @return array
     */
    private static function analysisPusher($message)
    {
        $members = [];
        $offset = 0;
        $len = mb_strlen($message);
        while ($offset < $len) {
            if ($message[$offset] === '@') {
                $offset++;
                $member = '';
                while (!in_array($message[$offset], [';', ' ', "\t", "\n", "\r"])) {
                    $member .= $message[$offset];
                    $offset++;
                }
                if ($member) {
                    $members[] = $member;
                }
            } else {
                $offset++;
            }
        }
        return $members;
    }

    /**
     * 查看用户是否存在
     * @param $handler //redis句柄
     * @param $member //用户成员名称
     * @return mixed
     */
    public static function memberExists(&$handler, $member)
    {
        return $handler->hashMGet(WB_MAP_NAME2ID, $member);
    }

    /**
     * 将事件push到用户的消息队列
     * @param $handler //redis句柄
     * @param $uid //用户内部id
     * @param $event //消息事件
     */
    public static function pushEvent2Member(&$handler, $uid, $event)
    {
        $key = WB_EVENT . $uid;
        $handler->listPush($key, $event);
    }

    /**
     * 将可能发送消息push给可能通知到的用户
     * @param $event //消息事件
     * @param $message //消息内容，用于解析可能将事件推送给who
     * @throws \yii\base\InvalidConfigException
     */
    public static function pushEvent2Members($event, $message = '')
    {
        if (!$message) {
            return;
        }
        $redis = Yii::$app->get('redis');
        $event_ = json_encode($event, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $members = self::analysisPusher($message);
        if (!$members || count($members) > EVENT_PUSH_UP_LIMIT) {
            return;
        }
        //遍历members, 获取有效member
        foreach ($members as $member) {
            if (($fid = self::memberExists($redis, $member))) {
                self::pushEvent2Member($redis, $fid, $event_);
            }
        }
    }

    public static function CommonEmpty($fields, &$params)
    {
        if(!$params || !$fields) {
            return true;
        }
        foreach ($fields as $_k => $item) {
            if(!isset($params[$_k])) {
                return true;
            }
            if($item) {
                if(!($params[$_k] = addslashes(trim($params[$_k])))) {
                    return true;
                }
            } else {
                if(!($params[$_k] = intval($params[$_k]))) {
                    return true;
                }
            }
        }
        return false;
    }

}