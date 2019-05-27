<?php


namespace app\controllers;


class ActionController extends DController
{

    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionLoginVerify()
    {

    }

    public function actionRegisterVerify()
    {

    }

}