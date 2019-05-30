<?php


namespace app\controllers;

use app\models\handler\SessionHandler;
use yii\web\Controller;
use Yii;
use RedisException;
class DController extends Controller
{

    public function beforeAction($action)
    {
        //连接redis
        try{
            $redis = Yii::$app->get('redis');
            $redis->connect();
        } catch (RedisException $ex) {
            Yii::$app->end();
        } catch (\Exception $ex2) {
            Yii::$app->end();
        }
        //连接mysql
        try{
            $mysql = Yii::$app->get('mysql');
            $mysql->connect();
        }catch (\Exception $e) {
            Yii::$app->end();
        }
        //使用redis存储session
        SessionHandler::init();
        session_start();
        return true;
    }
}