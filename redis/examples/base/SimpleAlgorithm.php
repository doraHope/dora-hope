<?php
namespace app\base;

class SimpleAlgorithm
{

    /**
     * 以interval的间隔标定采样精度, 单位为s
     */
    public static function getTimeInterval($interval)
    {
        $tiemstamp = time();
        return $tiemstamp - ($tiemstamp%$interval);
    }

}