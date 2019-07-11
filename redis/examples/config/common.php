<?php

//日志级别设置
defined('SEVERITY_INFO') or define('SEVERITY_INFO', '1');
defined('SEVERITY_NOTICE') or define('SEVERITY_NOTICE', '2');
defined('SEVERITY_WARINING') or define('SEVERITY_WARINING', '3');
defined('SEVERITY_ERROR') or define('SEVERITY_ERROR', '4');

defined('LOCK_TIMEOUT_SLOW') or define('LOCK_TIMEOUT_SLOW', 10);  //加锁时间长度

defined('LOCK_IS_NOT_ME') or define('LOCK_IS_NOT_ME', 404);