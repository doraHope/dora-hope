<?php


namespace app\models;


use yii\base\Model;

class UserLogin extends Model
{

    private $user;
    private $password;
    private $verifyCode;

    public function __set($name, $value)
    {
        switch ($name) {
            case 'user':
                $this->setUser($value);
                break;
            case 'password':
                $this->setPassword($value);
                break;
        }
    }

    public function __get($name)
    {
        switch ($name) {
            case 'user':
                $this->getUser();
                break;
            case 'password':
                $this->getPassword();
                break;
            case 'verifyCode':
                $this->getVerifyCode();
        }
    }

    public function setUser($value)
    {
        $this->user = $value;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function setVerifyCode($value)
    {
        $this->verifyCode = $value;
    }

    public function getVerifyCode()
    {
        return $this->verifyCode;
    }

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
            [['user', 'password', 'verifyCode'], 'require'],
            ['verifyCode', 'captcha']
        ];
    }
}