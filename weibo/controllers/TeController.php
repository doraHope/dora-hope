<?php


namespace app\controllers;

use yii\web\Controller;

class TeController extends Controller
{

    public function actionIndex()
    {
        return $this->render('t');
    }

}