<?php


namespace app\models\service;

use Yii;
class Email
{

    private static function arrayTrans2Messag($params)
    {
        $message = '';
        if($params['top']) {
            $message .= $params['top']."\n</br>";
        }
        if($params['body']) {
            $message .= "\t&emsp;".$params['body']."\n</br>";
        }
        if($params['footer']) {
            $message .= $params['footer']."\n</br>";
        }
        return $message;
    }

    public static function send($to, $title, $params)
    {
        $app = Yii::$app;
        $msg = self::arrayTrans2Messag($params);
        $email = $app->mailer->compose();
        $email->setTo($to);
        $email->setSubject($title);
        $email->setHtmlBody($msg);
        if($email->send()) {
            return true;
        }
        return false;
    }

}