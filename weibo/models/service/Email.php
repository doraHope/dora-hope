<?php


namespace app\models\service;

use Yii;
class Email
{
    private static $app = Yii::$app;

    public static function send($to, $title, $msg)
    {
        $email = Email::$app->mailer->compose();
        $email->setTo($to);
        $email->setSubject($title);
        $email->setHtmlBody($msg);
        if($email->send()) {
            return true;
        }
        return false;
    }

}