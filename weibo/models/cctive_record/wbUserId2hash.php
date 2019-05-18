<?php


namespace app\models\active_record;

use \app\models\base\Mysql;

/** 用户内部到外部映射关系表
 * Class wbUserId2hash
 * @package app\models\active_record
 */
class wbUserId2hash extends Mysql
{

    public $table = 'wb_user_id2hash';

}