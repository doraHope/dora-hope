<?php
namespace app\common;

class WBCommon
{

    public static function apiResponse($retCode = 0, $retMessage = '', $retData = [])
    {

        print(json_encode([
            'code' => $retCode,
            'msg' => $retMessage,
            'data' => $retData
        ], true));
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
}