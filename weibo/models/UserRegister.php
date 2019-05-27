<?php


namespace app\models\service;


class UserRegister
{

    private $user;
    private $password;
    private $email;

    public function __set($name, $value)
    {
        switch ($name) {
            case 'user':
                $this->setUser($value);
                break;
            case 'password':
                $this->setPassword($value);
                break;
            case 'email':
                $this->setEmail($value);
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

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getVerifyCode()
    {
        return $this->email;
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
            [['user', 'password', 'email'], 'require'],
            ['email', 'email']
        ];
    }

}