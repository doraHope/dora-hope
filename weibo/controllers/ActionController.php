<?php


namespace app\controllers;

use app\common\WBCommon;
use app\models\active_record\WbUserLogin;
use app\models\service\Email;
use app\models\UserLogin;
use app\models\UserRegister;
use Yii;
class ActionController extends DController
{

    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => null,
                'backColor' => 0XFFFFFF,
                'minLength' => 4,
                'maxLength' => 4,
                'transparent' => true,
                'height' => 40,
                'width' => 80
            ],
        ];
    }

    public function actionLoginVerify()
    {
        $app = Yii::$app;
        $strUser = addslashes($app->getRequest()->post('user', ''));
        $strPass = addslashes($app->getRequest()->post('password', ''));
        $strVerifyCode = addslashes($app->getRequest()->post('verifyCode', ''));
        $modelUserLogin = new UserLogin($strUser, $strPass, $strVerifyCode);
        if(!$modelUserLogin->validate()) {
            if($modelUserLogin->hasErrors()) {
                WBCommon::apiResponse(1, current($modelUserLogin->getErrors())[0]);
            }
        }
        print 'hope for you!';die;
        $modelUserLogin = new WbUserLogin();
        $ret = $modelUserLogin->verify($strUser, $strPass);
        if($ret['code']) {
            WBCommon::apiResponse(1, $ret['msg'], []);
        }
        $uid = intval($ret['data']['uid']);
        //写入数据库
        $timestamp = time();
        $ret = $modelUserLogin->update([
            'uid' => $uid
        ], ['update_time' => $timestamp]);
        if(!$ret) {
            //log
        }
        WBCommon::apiResponse(0, 'success', ['timestamp' => $timestamp]);
    }

    public function actionRegisterVerify()
    {
        $app = Yii::$app;
        $strUser = addslashes($app->getRequest()->post('user', ''));
        $strPass = addslashes($app->getRequest()->post('password', ''));
        $strEmail = addslashes($app->getRequest()->post('email', ''));
        $modelUserRegister = new UserRegister($strUser, $strPass, $strEmail);

        if(!$modelUserRegister->validate()) {
            if($modelUserRegister->hasErrors()) {
                WBCommon::apiResponse(1, current($modelUserRegister->getErrors())[0]);
            }
        }
        //向数据库插入一条记录
        $modelUserLogin = new WbUserLogin();
        $modelUserLogin->transaction();
        try{
            $retID = $modelUserLogin->queryPID('uid');
            if(false === $retID) {
                WBCommon::apiResponse(1, '获取用户id失败');
            }
            $timestamp = time();
            $strPass = WBCommon::hash($strPass);
            $ret = $modelUserLogin->write([
                'uid' => [0, $retID['uid']],
                'user' => [1, $strUser],
                'password' => [1, $strPass],
                'pre_login_time' => [0, $timestamp],
                'next_login_time' => [0, $timestamp]
            ]);
            if(!$ret) {
                WBCommon::apiResponse(1, '内部错误', []);
            }
            $modelUserLogin->commit();
        } catch (\Exception $e) {
            WBCommon::apiResponse(1, '数据库写入失败', []);
        }
        $uid = $retID['uid'];
        //使用redis生成一个token
        $link_ = WBCommon::createToken('hope for you!');
        $redis = $app->get('redis');
        $redis->stringSet($link_, $uid, 3600*12);
        //发送一条邮件
        $link = WB_URL.'/index.php/dora/succ2register/'.$link_;
        WBCommon::apiResponse(0, 'success', [$link]);
        Email::send($modelUserRegister->getEmail(), 'glad to tell you our register link!', $link);
    }
}