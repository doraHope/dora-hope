<?php


namespace app\models\base;


/**
 * 用户登陆数据统计基础算法类
 * @package app\models\base
 */
class LoginAnalyze
{

    public function __construct()
    {

    }

    //统计单个用户登陆日志数据，获取有用数据
    private function _analyzePoint(&$data)
    {
        $intTempMaxCount = 0;
        $intMaxCount = 0;
        $intMaxStart = PHP_INT_MIN;
        $intTempMaxStart = PHP_INT_MIN;
        $intMaxEnd = PHP_INT_MIN;
        $intTimeCount = 0;
        foreach ($data as $_k => $_v) {
            if($_k !== $intMaxStart+1) {
                $intTempMaxCount = 1;
                $intTempMaxStart = $_k;
            } else {
                $intTempMaxCount++;
            }
            if($intTempMaxCount > $intMaxCount) {
                $intMaxCount = $intTempMaxCount;
                $intMaxStart = $intTempMaxStart;
                $intMaxEnd = $_k;
            }
            $intTimeCount++;
        }
        return [
            'count' => $intTimeCount,
            'max_quantum' => [
                'start' => $intMaxStart,
                'end' => $intMaxEnd
            ]
        ];
    }

    private function _isLogin($redis, $uid, $timestamp)
    {
        if($redis->hashGet(\WeiBoConfig::$REDIS_KEY_NAMES['TODAY_LOGIN_USER_HASH'].':'.$uid, $timestamp)) {
            return true;
        } else {
            return false;
        }
    }

    //统计某个人在当天的登陆时间段
    public function tjUserLoginPoint($uid, $timestamp)
    {
        $redis = \Yii::app()->get('redis');
        $redis->connect();

        $log = $redis->hashGet(\WeiBoConfig::$REDIS_KEY_NAMES['LOGIN_LOG_HASH'].':'.$uid, $timestamp);
        if(!$log) {
            return [
                'count' => 0,
                'max_quantum' => [
                    'start' => '',
                    'end' => ''
                ]
            ];
        } else {
            $log = loginLogToArray($log);
            return $this->_analyzePoint($log);
        }
    }

    private function _setMapLogin(&$redis, $uid, $timestamp, &$map)
    {
        $log = $redis->hashGet(\WeiBoConfig::$REDIS_KEY_NAMES['LOGIN_LOG_HASH'].':'.$uid, $timestamp);
        if($log) {
            $log = loginLogToArray($log);
            foreach ($log as $_k => $_v) {
                ++$map[$_k];
            }
        }
    }

    public function _analyzeLoginMaxQuantum(&$map)
    {
        $maxTimeQuantum = NULL_THING;
        $maxTimeOnlineUsers = INT_DEFAULT;
        foreach ($map as $_k => $_v) {
            if($_v > $maxTimeOnlineUsers) {
                $maxTimeOnlineUsers = $_v;
                $maxTimeQuantum = $_k;
            }
        }
        return [
            'login_user_number' => $maxTimeOnlineUsers,
            'login_max_time_quantum' => $maxTimeQuantum
        ];
    }

    //统计所有用户当天的登陆时间段巅峰和登陆人数
    public function tjUserLoginOnlineMaxQuantum()
    {
        $redis = \Yii::app()->get('redis');
        $redis->connect();
        $mapLoginPoint = [];
        $timestamp = getTodayTimestamp();
        $offset = 0;
        $length = \WeiBoConfig::$lOGIN_LOAD_LIMIT;
        while(
            count(($arrUsersList = $redis->listRange(\WeiBoConfig::$REDIS_KEY_NAMES['TODAY_LOGIN_USER_LIST'], $offset, $length))) !== 0
        ) {
            foreach ($arrUsersList as $_k => $_v) {
                $this->_setMapLogin($redis, $_v, $timestamp, $mapLoginPoint);
            }
            $offset += $length;
        }
        $todayLoginInfo = $this->tjUserLoginOnlineMaxQuantum($mapLoginPoint);
        return [
            'login_quantum_point_info' => $mapLoginPoint,
            'login_user_max_quantum' => $todayLoginInfo
        ];
    }

}