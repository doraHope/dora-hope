<?php


namespace app\models\service;


class Filter
{
    public static function filterEmpty($params)
    {
        foreach ($params as $_k => $_v) {
            if(empty($_v)) {
                return false;
            }
        }
        return true;
    }
}