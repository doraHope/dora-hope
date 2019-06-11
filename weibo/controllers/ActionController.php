<?php


namespace app\controllers;

use app\common\FileHandler;
use app\common\SlowMqHandler;
use app\common\WBCommon;
use app\common\WBMessageCommon;
use app\common\WBRedisHandler;
use app\models\active_record\WbBase;
use app\models\active_record\WbReply;
use app\models\active_record\WbUserLogin;
use app\models\service\Email;
use app\models\UserLogin;
use app\models\UserRegister;
use app\models\active_record\WbComment;
use Yii;

/**
 * ajax操作响应类
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
        $strUser = addslashes($app->getRequest()->post('user', ''));
        $strPass = addslashes($app->getRequest()->post('password', ''));
        $strVerifyCode = addslashes($app->getRequest()->post('verifyCode', ''));
        $modelUserLogin = new UserLogin($strUser, $strPass, $strVerifyCode);
        if(!$modelUserLogin->validate()) {
            if($modelUserLogin->hasErrors()) {
                WBCommon::apiResponse(1, current($modelUserLogin->getErrors())[0]);
            }
        }
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

        }
        WBCommon::apiResponse(0, 'success', ['timestamp' => $timestamp]);
    }

    public function actionRegister()
    {

        $request = Yii::$app->getRequest();
        $mail = $request->post('mail');
        if($this->redis->stringGet(REGISTER_CODE.$mail)) {
            WBCommon::apiResponse(FAIL, '不能重复注册!');
        }
        $token = WBCommon::createToken('hope for you!');
        $link = WB_URL.'/index.php/miku/register?mail='.base64_encode(base64_encode('hope fo'.$mail.'r you!')).'&token='.$token;
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
     * 用户注册
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRegisterLogin()
    {
        $app = Yii::$app;
        $strUser = addslashes($app->getRequest()->post('user', ''));
        $strPass = addslashes($app->getRequest()->post('password', ''));
        $strEmail = addslashes($app->getRequest()->post('email', ''));
        $uid = -1;
        $modelUserRegister = new UserRegister($strUser, $strPass, $strEmail);
        if(!$modelUserRegister->validate()) {
            if($modelUserRegister->hasErrors()) {
                WBCommon::apiResponse(1, current($modelUserRegister->getErrors())[0]);
            }
        }
        //向数据库插入一条记录
        $modelUserLogin = new WbUserLogin();
        //开启事务且加锁的原因比较简单，避免事务处理中插入同一id
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
            $uid = $retID['uid'];
        } catch (\Exception $e) {
            WBCommon::apiResponse(1, '数据库写入失败', []);
        }

        $link_ = WBCommon::createToken('hope for you!');
        $redis = $app->get('redis');
        $redis->stringSet($link_, $uid, 3600*12);
        //发送一条邮件
        $link = WB_URL.'/index.php/dora/succ2register/'.$link_;
        WBCommon::apiResponse(0, 'success', [$link]);
        Email::send($modelUserRegister->getEmail(), 'glad to tell you our register link!', $link);
    }

    /**
     * 发送微博消息: 发送者，发送微博的内容[objWeiBo]
     */
    public function actionWeiBo()
    {
        $retData = [];
        $app = Yii::$app;
        $arrWeiBo = $app->getRequest()->post('obj_wei_bo', null);
        if(!$arrWeiBo) {
            WBCommon::apiResponse(1, '消息信息不完整');
        }
        $ret = WBMessageCommon::MSCorrect($arrWeiBo);
        if(false === $ret) {
            WBCommon::apiResponse(1, '消息不完整');
        }
        //@微博消息合理性AI校验, 较浅意义不大, 功能暂时搁浅
//        $arrWeiBo['uid'] = $app->session->get('uid');
        $arrWeiBo['uid'] = 1;
        if($arrWeiBo['extra_type'] === TYPE_FILE) {
            $retFile = FileHandler::moveUploadFile($arrWeiBo['uid'], $arrWeiBo['files']);
            if($retFile['fail']) {
                $retData['msg'] = '丢失文件包括';
                $retData['data'] = json_encode($retFile['fail'], JSON_UNESCAPED_UNICODE);
            }
            if(!$retFile['success']) {
                $arrWeiBo['extra_type'] = 0;
            } else {
                $arrWeiBo['extra_content'] = json_encode($retFile['success'], JSON_UNESCAPED_SLASHES);
            }
        }
        $arrWeiBo['openid'] = [0, $arrWeiBo['openid']];
        $arrWeiBo['content'] = [1, $arrWeiBo['content']];
        $arrWeiBo['extra_type'] = [0, $arrWeiBo['extra_type']];
        $arrWeiBo['uid'] = [0, $arrWeiBo['uid']];
        $arrWeiBo['last_time'] = [0, time()];
        $arrWeiBo['create_time'] = [0, time()];
        $modelWbBase = new WbBase();
        $modelWbBase->write($arrWeiBo);
        $intWbId = $modelWbBase->getInsertId();
        WBMessageCommon::pushEvent2Members([
            'event_type' => WB_EVENT_WEI_BO,
            'event_id' => $intWbId
        ], $arrWeiBo['content']);                                       //微博的消息中可能指定某个user，所以将该微博通知给user
        WBRedisHandler::push2selfWB($arrWeiBo['uid'][1], $intWbId);     //将微博推送到自己的微博消息队列中
        WBRedisHandler::pushWeiBo($arrWeiBo['uid'][1], $intWbId);       //将微博推送到粉丝的微博消息队列中

    }

    /**
     * 调试方法
     */
    public function actionDj()
    {
        SlowMqHandler::consumeMq(1);
    }

    /**
     * 评论
     */
    public function actionComment()
    {
        $request = Yii::$app->getRequest();
        $arrEvent = $request->post('comment');
        $timestamp = time();
//        $intUid = intval(Yii::$app->session->get('uid'));
        $intUid = 2;
        $ret = WBMessageCommon::CommonEmpty([
            'wb_id' => TYPE_INT,
            'content' => TYPE_STRING
        ], $arrEvent);
        if($ret) {
            WBCommon::apiResponse(FAIL, '评论失败!');
        }
        //微博是否存在校验
        $modelWbBase = new WbBase();
        $ret = $modelWbBase->queryByPkForExists(TYPE_INT, 'id', $arrEvent['wb_id']);
        if(!$ret) {
            WBCommon::apiResponse(FAIL, '该条微博不存在');
        }
        $modelWbComment = new WbComment();
        //写入评论
        $arrComment = [
            'wb_id' => [TYPE_INT, $arrEvent['wb_id']],
            'uid' => [TYPE_INT, $intUid],
            'comment_content' => [TYPE_STRING, $arrEvent['content']],
            'comment_time' => [TYPE_INT, $timestamp]
        ];
        $modelWbComment->write($arrComment);
        $commentId = $modelWbComment->getInsertId();
        $modelWB = new WbBase();
        $modelWB->update([
            'id' => [TYPE_INT, $arrEvent['wb_id']]
        ],  ['comment_number' => [TYPE_INT, 'comment_number+1']]);
        WBMessageCommon::pushEvent2Members([
            'event_type' => WB_EVENT_COMMENT,
            'event_id' => $commentId
        ], $arrEvent['content']);
        WBCommon::apiResponse(SUCCESS, 'success');
    }


    /**
     * 回复
     */
    public function actionRepay()
    {
        $request = Yii::$app->getRequest();
        $arrEvent = $request->post('reply');
        $timestamp = time();
        $intUid = intval(Yii::$app->session->get('uid'));
        $strOpenId = Yii::$app->session->get('openid');
        $ret = WBMessageCommon::CommonEmpty([
            'wb_id' => TYPE_INT,
            'comment_id' => TYPE_INT,
            'content' => TYPE_STRING
        ], $arrEvent);
        if($ret) {
            WBCommon::apiResponse(FAIL, '回复失败!');
        }
        $modelWbReply = new WbReply();
        $ret = $modelWbReply->queryByPkForExists(TYPE_INT, 'id', $arrEvent['comment_id']);
        if(!$ret) {
            WBCommon::apiResponse(FAIL, '该条评论不存在!');
        }
        $arrReply = [
            'comment_id' => [TYPE_INT, $arrEvent['comment_id']],
            'uid' => [TYPE_INT, $intUid],
            'openid' => [TYPE_STRING, $strOpenId],
            'reply_content' => [TYPE_STRING, $arrEvent['content']],
            'reply_time' => $timestamp
        ];
        $modelWbReply->write($arrReply);
        $replyId = $modelWbReply->getInsertId();
        $modelComment = new WbComment();
        $modelComment->update([
            'id' => [TYPE_INT, $arrEvent['comment_id']]
        ], ['reply_number' => [TYPE_INT, 'reply_number+1']]);
        $modelWB = new WbBase();
        $modelWB->update([
            'id' => [TYPE_INT, $arrEvent['wb_id']]
        ],  ['comment_number' => [TYPE_INT, 'comment_number+1']]);
        WBMessageCommon::pushEvent2Members([
            'event_type' => WB_EVENT_COMMENT,
            'event_id' => $replyId
        ], $arrEvent['content']);
        WBCommon::apiResponse(SUCCESS, 'success');
    }

}