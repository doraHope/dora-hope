<?php


namespace app\models\exception;


class EventException extends \Exception
{

    public function getExceptionInfo()
    {
        return '[EventException]: "'.$this->getMessage().'"'.PHP_EOL;
    }

}