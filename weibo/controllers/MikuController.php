<?php


namespace app\controllers;

use app\common\WBCommon;
use app\models\service\Filter;
use Yii;
/**
 * @package app\controllers
 */
class MikuController extends DController
{

    public function actionRegister()
    {
        $request = Yii::$app->getRequest();
        $mail = $request->get('mail');
        $token = $request->get('token');
        $ret = Filter::filterEmpty([
            'mail' => $mail,
            'token' => $token
        ]);
        if(!$ret) {
            WBCommon::apiResponse(FAIL, '缺少必要参数');
        }
        $rToken = $this->redis->stringGet(REGISTER_CODE.$mail);
        if(!$rToken || $rToken != $token) {
            return $this->renderPartial('@app/views/error/404');
        }
        $token = WBCommon::createToken('hope for you!');
        $this->redis->stringSet(REGISTER_SUCC.$mail, $token, 3600*2);
        $this->session->set('token', $token);
        $this->session->set('mail', $mail);
        $this->layout = 'hope';
        return $this->render('register', [
            'mail' => $mail
        ]);
    }

}