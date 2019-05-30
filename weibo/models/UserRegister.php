<?php


namespace app\models;


use yii\base\Model;

class UserRegister extends Model
{

    private $user;
    private $password;
    private $email;

    public function __construct($user, $password, $email)
    {
        $this->user = $user;
        $this->password = $password;
        $this->email = $email;
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
//            case 'email':
//                $this->setEmail($value);
//        }
//    }

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
            case 'email':
                $value = $this->getEmail();
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
//    public function setEmail($value)
//    {
//        $this->email = $value;
//    }

    public function getEmail()
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
            [['user', 'password', 'email'], 'required', 'message' => '注册信息不能为空'],
            ['email', 'email']
        ];
    }

}