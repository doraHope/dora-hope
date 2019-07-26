<?php

//日志级别设置
defined('SEVERITY_INFO') or define('SEVERITY_INFO', '1');
defined('SEVERITY_NOTICE') or define('SEVERITY_NOTICE', '2');
defined('SEVERITY_WARINING') or define('SEVERITY_WARINING', '3');
defined('SEVERITY_ERROR') or define('SEVERITY_ERROR', '4');

defined('LOCK_TIMEOUT_SLOW') or define('LOCK_TIMEOUT_SLOW', 10);  //加锁时间长度

defined('LOCK_IS_NOT_ME') or define('LOCK_IS_NOT_ME', 404);

defined('EVENT_HIT') or define('EVENT_HIT', 1);                     //访问页面的点击事件
defined('EVENT_MYSQL_READ') or define('EVENT_MYSQL_READ', 2);       //mysql的读事件
defined('EVENT_MYSQL_WRITE') or define('EVENT_MYSQL_WRITE', 3);     //mysql的写事件
defined('MAP_EVENT') or define('MAP_EVENT', [
    EVENT_HIT => 'hits',
    EVENT_MYSQL_READ => 'read',
    EVENT_MYSQL_WRITE => 'write'
]);
defined('MAP_COUNTER_CLEAN') or define('MAP_COUNTER_CLEAN', [
    EVENT_HIT => 'record:simple',
    EVENT_MYSQL_READ => 'record:simple',
    EVENT_MYSQL_WRITE => 'record:simple',
]);