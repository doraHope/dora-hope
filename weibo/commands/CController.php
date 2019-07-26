<?php


namespace app\commands;


use RedisException;
use Yii;
use yii\console\Controller;

/**
 * 队列消费基类
 * Class CController
 * @package app\commands
 */
class CController extends Controller
{
    public $redis;
    public $mysql;

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
        return true;
    }

}