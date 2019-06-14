<?php


namespace app\models\active_record;

use \app\models\base\Mysql;
use phpDocumentor\Reflection\DocBlock\Tags\Param;

/** 用户登陆表
 * Class WbUserLogin
 * @package app\models\active_record
 */
class WbUserLogin extends Mysql
{

    public $table = 'wb_user_login';

    public function verify($user, $pass)
    {
        $userInfo = $this->queryOneByKey(TYPE_STRING, 'user', $user);
        if(!$userInfo) {
            return false;
        }
        if($userInfo['password'] !== $pass) {
            return false;
        }
        return $userInfo['uid'];
    }

    public function queryPID($key)
    {
        return parent::queryPID($key);
    }

}