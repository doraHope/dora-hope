<?php
use app\redis\RedisSortSet;
use app\redis\RedisHash;

/**
 * 计数器清除器
 */
class CounterClean
{

    private $record;             //关联到redis中的用于存储在用计数器的信息
    private $interval;          //处理周期
    private $timer;             //时间计数器

    public function __construct($key, $interval)
    {
        $this->record = new RedisSortSet($key);    
        $this->interval = $interval;
        $this->timer = 0;
    }

    private function _run($counter, $timestamp)
    {
        $hashCounter = new RedisHash($counter);
        $data = $hashCounter->getAll();
        $cleanKeys = array_keys($data);
        $index = 0;
        foreach ($cleanKeys as $key) {
            if ($key < $timestamp) {
                $hashCounter->rem($key);
                unset($cleanKeys[$index]);
            }
            $index++;
        }
        //如果该计时器已经被清空，则清空计数器的record中的对应项
        if(!$cleanKeys) {
            $this->record->rem(substr($counter, 7));
            return true;
        }
        krsort($cleanKeys);
        $reCleanKeys = array_slice($cleanKeys, 120);
        foreach ($reCleanKeys as $key) {
            $hashCounter->rem($key);
        }
        return false;
    }

    /**
     * 由计数器精度确定刷新的时间间隔
     */
    private function _interval($interval)
    {
        if($interval < 60) {
            return 1;
        }
        if($interval < 1800) {
            return 5;
        }
        if($interval < 3600) {
            return 10;
        }
        if($interval < 3600*12) {
            return 60;
        }
        return 1440;
    }

    private function _prepare($list)
    {
        $map = [];
        foreach ($list as $key => $item) {
            list($name, $interval) = explode(':', $key);
            $map['counter:'.$key] = $this->_interval($interval);
        }
        return $map;
    }
  
    //脚本主入口
    public function main()
    {
        $listCounter = $this->record->range(0, -1);
        $mapCounter = $this->_prepare($listCounter);
        $mapFinished = [];
        while(true) {
            $timestamp = time();
            $finished = true;
            foreach($mapCounter as $counter => $interval) {
                if(isset($mapFinished[$counter])) {
                    continue;
                } 
                $finished = false;
                //当前进度为为interval的整数倍，则对对应的计数器进行清除
                if($this->timer%$interval === 0) {
                    $ret = $this->_run($counter, $timestamp);
                    if($ret) {
                        unset($mapCounter[$counter]);
                    }
                    $mapFinished[$counter] = true;
                }
                usleep(10000);
            }
            $this->timer++;
            if($finished) {
                unset($listCounter);
                unset($mapCounter);
                $listCounter = $this->record->range(0, -1);
                $mapCounter = $this->_prepare($listCounter);
                $this->timer = 0;
            }
            sleep(5);
        }
    }
}