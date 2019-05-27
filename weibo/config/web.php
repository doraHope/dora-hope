<?php

$params = require __DIR__ . '/params.php';

$config = [
    'id' => 'wb',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@app' => dirname(__DIR__)
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '7eqrjLvGWTu4JxLkbsP5XwFkZR65IejU',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.163.com',
                'username' => 'dora_Miku@163.com',
                'password' => 'chuyinwl070301',
                'encryption' => 'tls'
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => ['dora_Miku@163.com' => 'hope']
            ]
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
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
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>'
            ],
        ],
    ],
    'params' => $params,
];

return $config;
