<?php


namespace app\controllers;


use app\common\FileHandler;
use app\common\WBCommon;
use app\common\WBMessageCommon;
use app\common\WBRedisHandler;
use app\models\active_record\WbBase;
use app\models\active_record\WbComment;
use app\models\active_record\WbReply;
use Yii;

class WbController extends HopeController
{


    public function actionQueryWeiBo()
    {
        $offset = $this->request->get('offset', 0);
        $uid = $this->session->get('uid');
        $arrWbIds = $this->redis->listRange(WB_PULIBC.$uid, $offset, \WeiBoConfig::$PER_LOAD_WB);
        if(!$arrWbIds) {
            WBCommon::apiResponse(SUCCESS);
        }
        $modelWeiBo = new WbBase();
        $result = $modelWeiBo->query(sprintf('select u.openid, u.nickname, w.* from (select * from wb_base where id in (%s)) as w join wb_user u on w.uid = u.uid order by w.id desc', implode(', ', $arrWbIds)));
        if($result) {
            foreach ($result as &$item) {
                $item['create_time'] = date('Y-m-d H:i', $item['create_time']);
                $item['last_time'] = date('Y-m-d H:i', $item['last_time']);
            }
            WBCommon::apiResponse(SUCCESS, '', $result);
        }
        WBCommon::apiResponse(FAIL, '');
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
        $arrWeiBo['uid'] = $app->session->get('uid');
        if(isset($objWB['extra_type']) && $arrWeiBo['extra_type'] === TYPE_FILE) {
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
        ], $arrWeiBo['content'][1]);                                       //微博的消息中可能指定某个user，所以将该微博通知给user
//        WBRedisHandler::pushWeiBo($arrWeiBo['uid'][1], $intWbId);
        WBRedisHandler::push2CommonWB($arrWeiBo['uid'][1], $intWbId);       //将微博推送到自己的微博消息队列中
        WBRedisHandler::pushWeiBo($arrWeiBo['uid'][1], $intWbId);           //将微博推送到粉丝的微博消息队列中
        WBCommon::apiResponse(SUCCESS);
    }

    /**
     * 关注他人
     */
    public function actionFocus()
    {
        //todo
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