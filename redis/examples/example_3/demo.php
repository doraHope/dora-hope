<?php
//统计器 Counter
namespace app\example_3;

ini_set('include_path', '/var/www/dora/redis/redis_base'.PATH_SEPARATOR.'/var/www/dora/redis/examples/base');
require './Counter.php';
require 'RedisBase.php';
require 'RedisString.php';
require 'RedisHash.php';
require 'RedisSortSet.php';
require 'RedisConfig.php';
require 'Log.php';
require 'DataUtil.php';
require 'Response.php';
require 'ParamsUtil.php';
require 'SimpleAlgorithm.php';
require '../config/common.php';

$counter = new Counter(EVENT_HIT);
$counter->count(10);