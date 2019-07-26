<?php


namespace app\controllers;

use app\common\FileHandler;
use app\common\SlowMqHandler;
use app\common\WBCommon;
use app\common\WBMessageCommon;
use app\common\WBRedisHandler;
use app\models\active_record\WbBase;
use app\models\active_record\WbConfigMessageAite;
use app\models\active_record\WbConfigMessageComment;
use app\models\active_record\WbConfigMessageLike;
use app\models\active_record\WbConfigMessagePrivate;
use app\models\active_record\WbConfigPrivacy;
use app\models\active_record\WbReply;
use app\models\active_record\WbUser;
use app\models\active_record\WbUserCareer;
use app\models\active_record\WbUserEducation;
use app\models\active_record\WbUserId2Hash;
use app\models\active_record\WbUserLabel;
use app\models\active_record\WbUserLogin;
use app\models\active_record\WbUserRank;
use app\models\service\Email;
use app\models\UserLogin;
use app\models\UserRegister;
use Yii;

/**
 * ajax操作响应类    用户登陆操纵相关
 * @package app\controllers
 */
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

    /**
     * 登陆校验
     */
    public function actionLoginVerify()
    {
        $app = Yii::$app;
        $strUser = addslashes($app->getRequest()->post('username', ''));
        $strPass = addslashes($app->getRequest()->post('password', ''));
//        $strVerifyCode = addslashes($app->getRequest()->post('verifyCode', ''));
        $modelUserLogin = new UserLogin($strUser, $strPass);
        if(!$modelUserLogin->validate()) {
            if($modelUserLogin->hasErrors()) {
                WBCommon::apiResponse(1, current($modelUserLogin->getErrors())[0]);
            }
        }
        $modelUserLogin = new WbUserLogin();
        $uid = $modelUserLogin->verify($strUser, WBCommon::hash($strPass));
        if(!$uid) {
            WBCommon::apiResponse(FAIL, '登陆失败');
        }
        $ret = $modelUserLogin->update(['uid' => [TYPE_INT, $uid]], [
            'pre_login_time' => [TYPE_INT, 'last_login_time'],
            'last_login_time' => [TYPE_INT, time()]
        ]);
        if(!$ret) {
            //log
        }
        $modelUser = new WbUser();
        $arrUserInfo = $modelUser->queryOneByKey(TYPE_STRING, 'uid', $ret);
        if(!$arrUserInfo) {
            WBCommon::apiResponse(FAIL, '用户不存在!');
        }
        $this->session->set('mail', $strUser);
        $this->session->set('uid', $uid);
        $this->session->set('nickname', $arrUserInfo['nickname']);
        WBCommon::apiResponse(SUCCESS);
    }

    public function actionLogout()
    {
        if($this->session->get('uid')) {
            $this->session->set('uid', '');
            $this->session->set('nickname', '');
            $this->session->set('mail', '');
            WBCommon::apiResponse(SUCCESS);
        } else {
            WBCommon::apiResponse(FAIL, '注销失败');
        }
    }

    public function actionRegister()
    {
        $request = Yii::$app->getRequest();
        $mail = $request->post('mail');
        if($this->redis->stringGet(REGISTER_CODE.$mail)) {
            WBCommon::apiResponse(FAIL, '不能重复注册!');
        }
        $token = WBCommon::createToken('hope for you!');
        $link = WB_URL.'/index.php/miku/register?token='.$token.'&mail='.base64_encode(base64_encode('hope fo'.$mail.'r you!'));
        $content = [
            'top' => '您好! 先生/女士，欢迎注册本网站，您的邀请码有效时间为3小时，请尽快进行注册',
            'body' => $link,
            'footer' => '五运昌隆'
        ];
        $ret = Email::send($mail, 'glad to tell you our register link!', $content);
        if($ret) {
            if(false !== $this->redis->stringSet(REGISTER_CODE.$mail, $token, 3600*3)) {
                WBCommon::apiResponse(SUCCESS);
            } else {
                WBCommon::apiResponse(FAIL, 'token生成失败,请稍后重试!');
            }
        } else {
            WBCommon::apiResponse(FAIL, '发送邮件失败');
        }
    }

    /**
     * 用户注册成功后，初始化用户信息到数据库
     */
    public function actionRegisterVerify()
    {
        $app = Yii::$app;
        $strNickname = addslashes(trim($app->getRequest()->post('nickname', '')));
        $strMail = addslashes(trim($app->getRequest()->post('mail', '')));
        $strPass = addslashes($app->getRequest()->post('password', ''));
        $modelUserRegister = new UserRegister($strMail, $strPass, $strNickname);
        //传参合法性校验
        if(!$modelUserRegister->validate()) {
            if($modelUserRegister->hasErrors()) {
                WBCommon::apiResponse(FAIL, current($modelUserRegister->getErrors())[0]);
            }
        }
        $sessMail = $this->session->get('mail');
        if(!$sessMail || $strMail !== $sessMail) {
            WBCommon::apiResponse(FAIL, '邮箱不为注册邮箱');
        }
        $sessToken = $this->session->get('token');
        $rToken = $this->redis->stringGet(REGISTER_SUCC.$sessMail);
        if(!$rToken || $sessToken !== $rToken) {
            WBCommon::apiResponse(FAIL, 'token 过期，请重新注册!');
        }
        //向数据库插入一条记录
        $modelUser = new WbUser();
        //开启事务且加锁的原因比较简单，避免事务处理中插入同一id
        $modelUser->transaction();
        try{
            //将用户数据库初始化到数据库中
            $uid = ($modelUser->queryPID('uid'));
            if(!$uid) {
                //消回用户注册token
                $modelUser->rollback();
                WBCommon::apiResponse('注册失败, 请稍后重新注册');
            }
            $timestamp = time();
            $openid = WBCommon::createOpenid(SALT, $sessMail);
            $ret = $modelUser->write([
                'uid' => [TYPE_INT, $uid],
                'openid' => [TYPE_STRING, $openid],
                'nickname' => [TYPE_STRING, $strNickname],
                'birthday' => [TYPE_INT, $timestamp],
                'register_time' => [TYPE_INT, $timestamp]
            ]);
            if(!$ret) {
                $modelUser->rollback();
                WBCommon::apiResponse('注册失败, 请稍后重新注册');
            }
            //初始化用户表中对应的记录
            $modelWbConfigAite = new WbConfigMessageAite();
            $modelWbConfigAite->write([
                'uid' => [TYPE_INT, $uid]
            ]);
            $modelWbConfigComment = new WbConfigMessageComment();
            $modelWbConfigComment->write([
                'uid' => [TYPE_INT, $uid]
            ]);
            $modelWbConfigLike = new WbConfigMessageLike();
            $modelWbConfigLike->write([
                'uid' => [TYPE_INT, $uid]
            ]);
            $modelWbConfigPrivate = new WbConfigMessagePrivate();
            $modelWbConfigPrivate->write([
                'uid' => [TYPE_INT, $uid]
            ]);
            $modelWbConfigPrivacy = new WbConfigPrivacy();
            $modelWbConfigPrivacy->write([
                'uid' => [TYPE_INT, $uid]
            ]);
            $modelUserCareer = new WbUserCareer();
            $modelUserCareer->write([
                'uid' => [TYPE_INT, $uid]
            ]);
            $modelUserEducation = new WbUserEducation();
            $modelUserEducation->write([
                'uid' => [TYPE_INT, $uid]
            ]);
            $modelUserLabel = new WbUserLabel();
            $modelUserLabel->write([
                'uid' => [TYPE_INT, $uid],
                'labels' => [TYPE_STRING, '[]']
            ]);
            $modelUserLogin = new WbUserLogin();
            $modelUserLogin->write([
                'uid' => [TYPE_INT, $uid],
                'user' => [TYPE_STRING, $strMail],
                'password' => [TYPE_STRING, WBCommon::hash($strPass)],
                'status' => [TYPE_INT, 1],
                'pre_login_time' => [TYPE_INT, time()],
                'last_login_time' => [TYPE_INT, time()]
            ]);
            $modelUserRank = new WbUserRank();
            $modelUserRank->write([
                'uid' => [TYPE_INT, $uid],
                'rank' => [TYPE_STRING, '[]']
            ]);
            $modelUserId2Openid = new WbUserId2Hash();
            $modelUserId2Openid->write([
                'uid' => [TYPE_INT, $uid],
                'openid' => [TYPE_STRING, $openid]
            ]);
            $modelUser->commit();
            WBCommon::apiResponse(SUCCESS);
        } catch (\Exception $ex) {
            $modelUser->rollback();
            WBCommon::apiResponse('注册失败, 请稍后重新注册');
        }
    }

}