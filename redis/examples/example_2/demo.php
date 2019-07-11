<?php
//日志
namespace app\example_2;

ini_set('include_path', '/var/www/dora/redis/redis_base'.PATH_SEPARATOR.'/var/www/dora/redis/examples/base');
require 'InputMatching.php';
require 'RedisBase.php';
require 'RedisString.php';
require 'RedisList.php';
require 'RedisConfig.php';
require 'Log.php';
require 'DataUtil.php';
require 'Response.php';
require 'ParamsUtil.php';
require 'LogOfRedis.php';

$objLog = new LogOfRedis(SEVERITY_INFO);
$objLog->log('今天先到这儿');
