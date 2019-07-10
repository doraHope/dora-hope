<?php


namespace app\redis;

use \Exception;
class RException extends Exception
{

    public function getClass()
    {
        return __CLASS__;
    }

}