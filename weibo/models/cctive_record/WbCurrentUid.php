<?php


namespace app\models\active_record;

use \app\models\base\Mysql;

/** 用户唯一标识id生成器
 * Class WbCurrentUid
 * @package app\models\active_record
 */
class WbCurrentUid extends Mysql
{

    public $table = 'wb_current_uid';

}