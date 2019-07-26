<?php

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'mysql' => [
            'class' => 'app\models\base\Mysql',
            'host' => \WeiBoConfig::$MYSQL_CONFIG['url']['host'],
            'port' => \WeiBoConfig::$MYSQL_CONFIG['url']['port'],
            'user' => \WeiBoConfig::$MYSQL_CONFIG['auth']['user'],
            'password' => \WeiBoConfig::$MYSQL_CONFIG['auth']['password'],
            'db' => \WeiBoConfig::$MYSQL_CONFIG['option']['db'],
        ],
        'redis' => [
            'class' => 'app\models\base\Redis',
            'host' => \WeiBoConfig::$REDIS_CONFIG['url']['host'],
            'port' => \WeiBoConfig::$REDIS_CONFIG['url']['port'],
            'user' => \WeiBoConfig::$REDIS_CONFIG['auth']['user'],
            'password' => \WeiBoConfig::$REDIS_CONFIG['auth']['password'],
            'pconnect' => \WeiBoConfig::$REDIS_CONFIG['option']['pconnect'],
            'timeout' => \WeiBoConfig::$REDIS_CONFIG['option']['timeout'],
            'retryInterval' => \WeiBoConfig::$REDIS_CONFIG['option']['retry_interval'],
            'readTimeout' => \WeiBoConfig::$REDIS_CONFIG['option']['read_timeout']
        ],
    ],
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];


return $config;
