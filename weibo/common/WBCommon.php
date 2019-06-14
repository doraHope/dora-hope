<?php
namespace app\common;

use Yii;
class WBCommon
{

    public static function apiResponse($retCode = 0, $retMessage = '', $retData = [])
    {

        print(json_encode([
            'code' => $retCode,
            'msg' => $retMessage,
            'data' => $retData
        ], JSON_UNESCAPED_UNICODE));
        Yii::$app->end();
    }

    private static function _randomStr()
    {
        $words = 'qwertyuiiopasdfghjklzxcvbnm1234567890';
        $ret = '';
        for($i = 0; $i < 16; $i++) {
            $ret .= $words[rand(0, strlen($words)-1)];
        }
        return $ret;
    }

    public static function createToken($salt)
    {
        $text = WBCommon::_randomStr().'_'.time().'_'.$salt.'_hope';
        return substr(sha1($text), 8, 24);
    }

    public static function hash($text)
    {
        return substr(sha1(substr(SALT, 4, 5).'_'.$text.'_'.substr(5, 4)), 4, 32);
    }

    public static function createOpenid($salt, $content)
    {
        return 'HOPE_'.substr(sha1($salt.'_'.time().$content), 13, 23);
    }
}