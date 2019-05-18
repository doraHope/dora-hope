<?php


namespace app\models\active_record;

use \app\models\base\Mysql;

/** 用户消息设置"赞相关"
 * Class WbConfigMessageLike
 * @package app\models\active_record
 */
class WbConfigMessageLike extends Mysql
{
    public $table = 'wb_config_message_like';
}