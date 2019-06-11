<?php


namespace app\controllers;

use app\models\handler\SessionHandler;
use yii\web\Controller;
use Yii;
use RedisException;
class DController extends Controller
{

    public $redis;
    public $mysql;
    public $session;

    public function beforeAction($action)
    {
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
        //使用redis存储session
        SessionHandler::init();
        session_start();
        return true;
    }
}