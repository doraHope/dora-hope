<?php


namespace app\common;

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
        if(!isset($objWB['openid']) || !$objWB) {
            return false;
        }
        $objWB['openid'] = addslashes($objWB['openid']);
        $objWB['content'] = trim($objWB['content']);
        if(!isset($objWB['content']) || !$objWB['content']) {
            return false;
        }
        $objWB['content'] = addslashes($objWB['openid']);
        if(($objWB['extra_type'] = intval($objWB['extra_type'])) === TYPE_FILE) {
            if(!$_FILES['extra_file']) {
                $objWB['extra_type'] = TYPE_TEXT;
            } else {
                $objWB['files'] = [];
                foreach ($_FILES['extra_file'] as $file) {
                    $objWB['files'] = $file;
                }
            }
        }
        return true;
    }

}