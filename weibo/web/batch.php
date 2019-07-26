<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../config/WeiBoConfig.php';
require __DIR__ . '/../config/define.php';
require __DIR__ . '/../config/common.function.php';


$config = require __DIR__ . '/../config/console.php';


(new yii\console\Application($config))->run();
