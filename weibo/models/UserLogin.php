<?php


namespace app\models;


use yii\base\Model;

class UserLogin extends Model
{

    private $user;
    private $password;
    private $verifyCode;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
//        $this->verifyCode = $verifyCode;
    }

//    public function __set($name, $value)
//    {
//        switch ($name) {
//            case 'user':
//                $this->setUser($value);
//                break;
//            case 'password':
//                $this->setPassword($value);
//                break;
//        }
//    }
//
    public function __get($name)
    {
        $value = '';
        switch ($name) {
            case 'user':
                $value = $this->getUser();
                break;
            case 'password':
                $value = $this->getPassword();
                break;
//            case 'verifyCode':
//                $value = $this->getVerifyCode();
        }
        return $value;
    }

//    public function setUser($value)
//    {
//        $this->user = $value;
//    }
//
//    public function setPassword($value)
//    {
//        $this->password = $value;
//    }
//
//    public function setVerifyCode($value)
//    {
//        $this->verifyCode = $value;
//    }

//    public function getVerifyCode()
//    {
//        return $this->verifyCode;
//    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function rules()
    {
        return [
            [['user', 'password'], 'required', 'message' => '登陆信息不能为空']
//            ['verifyCode', 'captcha',  'captchaAction' => 'action/captcha']
        ];
    }
}