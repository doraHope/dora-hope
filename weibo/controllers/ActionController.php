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
        $ret = $modelUserLogin->verify($strUser, WBCommon::hash($strPass));
        if(!$ret) {
            WBCommon::apiResponse(FAIL, '登陆失败');
        }
        $modelUserLogin->update(['uid' => [TYPE_INT, $ret]], [
            'pre_login_time' => [TYPE_INT, 'last_login_time'],
            'last_login_time' => [TYPE_INT, time()]
        ]);
        if(!$ret) {
            //log
        }
        WBCommon::apiResponse(SUCCESS);
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
            $uid = ($modelUser->queryPID('id'))['id'];
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