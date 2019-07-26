<?php

namespace app\example_2;

use app\redis\RedisList;
use app\redis\RedisString;
use app\redis\RedisBase;
use app\base\Log;

class LogOfRedis
{

    private $severity;          //日志等级
    
    private $log;               //日志
    private $logTime;           //日志版本事件标识

    const SERVERITY = [         //日志等级rank
        SEVERITY_INFO,
        SEVERITY_NOTICE,
        SEVERITY_WARINING,
        SEVERITY_ERROR
    ];

    public function __construct($severity)
    {
        if(!in_array($severity, self::SERVERITY)) {
            throw new Exception("错误日志级别");
        }
        $this->severity = $severity;
        //建立对象将 对象关联到指定key
        $this->log = new RedisList(sprintf('common:log:%s', $this->severity));
        $this->logTime = new RedisString(sprintf('common:log_time:%s', $this->severity));
    }

    /**
     * 日志备份，每隔一小时一次
     * 备份内容包括日志列表本身和日志版本号
     */
    private function _save($hour)
    {
        $key = RedisBase::multi('log');
        $this->log->rename(sprintf('common:log:%s:last', $this->severity));
        $this->logTime->rename(sprintf('common:log_time:%s:last', $this->severity));
        $this->logTime->set($hour);
        $ret = RedisBase::exec('log', $key);
        if(LOCK_IS_NOT_ME === $ret) {
            //todo log
        } else if(false === $ret) {
            //todo log
        }
    }

    /**
     * 将日志写到列表开头，并对日志列表进行修剪
     */
    private function _write($event)
    {
        $this->log->lPush($event);
        $this->log->trim(0, 99);
    }

    /**
     * 打log
     */
    public function log($message)
    {
        $event = [
            'date' => date('Y-m-d H:i:s', time()),
            'content' => $message
        ];
        $oldHour = $this->log->get();
        $nowHour = intval(date('H', time()));
        if(false === $oldHour) {
            $this->logTime->set($nowHour);
            $oldHour = $nowHour;
        } else {
            $oldHour = intval($oldHour);
        }
        if($oldHour !== $nowHour) {
            $this->_save($nowHour);
        } 
        $this->_write(json_encode($event, JSON_UNESCAPED_UNICODE));
    }
}