<?php


namespace app\controllers;


use app\models\handler\SessionHandler;
use RedisException;
use Yii;
use yii\web\Controller;

/**
 * 用作后台控制器基类
 * Class HopeController
 * @package app\controllers
 */
class HopeController extends Controller
{

    public $redis;
    public $mysql;
    public $session;
    public $request;

    public function beforeAction($action)
    {
        if(Yii::$app->session->get('uid')) {
            return false;
        }
        //连接redis
        try{
            $redis = Yii::$app->get('redis');
            $redis->connect();
            $this->redis = $redis;
        } catch (RedisException $ex) {
            Yii::$app->end();
        } catch (\Exception $ex2) {
            Yii::$app->end();
        }
        //连接mysql
        try{
            $mysql = Yii::$app->get('mysql');
            $mysql->connect();
            $this->mysql = $mysql;
        }catch (\Exception $e) {
            Yii::$app->end();
        }
        $this->session = Yii::$app->session;
        $this->request = Yii::$app->getRequest();
        //使用redis存储session
        SessionHandler::init();
        session_start();
        return true;
    }

}