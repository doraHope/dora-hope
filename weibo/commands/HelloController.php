<?php


namespace app\commands;



class HelloController extends CController
{

    public function actionIndex()
    {
        echo 'hello world';
    }

}