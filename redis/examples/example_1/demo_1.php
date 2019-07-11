<?php
//输入匹配

namespace app\example_1;

ini_set('include_path', '/var/www/dora/redis/redis_base'.PATH_SEPARATOR.'/var/www/dora/redis/examples/base');
require 'InputMatching.php';
require 'RedisBase.php';
require 'RedisSortSet.php';
require 'RedisConfig.php';
require 'Log.php';
require 'DataUtil.php';
require 'Response.php';
require 'ParamsUtil.php';

$match = new InputMatching();
$match->matching('ab');