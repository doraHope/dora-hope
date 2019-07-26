<?php


namespace app\models;


use yii\base\Model;

class WBComment extends Model
{



    public function rules()
    {
        return [
            ['wbId, openId, comment', 'required', 'message' => '无效评论'],

        ];
    }

}