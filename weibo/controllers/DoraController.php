<?php
namespace app\controllers;

use yii\web\Controller;

/** 默认首页（前端）
 * 1、查看网站适用情况
 * 2、微博排行榜，(热搜先摆摆)
 * 3、用户认证、微博消息筛查处理
 * Class DoraController
 * @package app\controllers
 */
class DoraController extends DController
{

    public $layout = false;




    //默认的首页
    public function actionIndex()
    {
        //设置选中的导航栏
        $this->view->params['default_nav'] = 'user/index';      //设置选中的导航栏
        $this->view->params['select_nav'] = 'user/manage';      //设置选中导航栏中的子项
        $this->view->params['title'] = '微博后台管理-流量分析';   //网页标题
        $this->layout = 'hope';                                 //设置布局文件
//        $this->layout = false;
        return $this->render('default_page');
    }

    //注册
    public function actionRegister()
    {

    }
}