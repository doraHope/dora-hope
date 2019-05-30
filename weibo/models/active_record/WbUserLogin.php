<?php


namespace app\models\active_record;

use \app\models\base\Mysql;

/** 用户登陆表
 * Class WbUserLogin
 * @package app\models\active_record
 */
class WbUserLogin extends Mysql
{

    public $table = 'wb_user_login';

    public function verify($user, $pass)
    {

    }

    public function queryPID($key)
    {
        return parent::queryPID($key);
    }

}