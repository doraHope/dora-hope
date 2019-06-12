<?php


namespace app\models;


use yii\base\Model;

class UserRegister extends Model
{

    private $password;
    private $email;
    private $nickname;

    public function __construct($email, $password, $nickname)
    {
        $this->password = $password;
        $this->email = $email;
        $this->nickname = $nickname;
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
            case 'nickname':
                $value = $this->getNickname();
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

    public function getNickname()
    {
        return $this->nickname;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function rules()
    {
        return [
            [['password', 'email', 'nickname'], 'required', 'message' => '注册信息不能为空'],
            ['email', 'email']
        ];
    }

}