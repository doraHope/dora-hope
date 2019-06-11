<?php

class WeiBoConfig
{

    public static $lOGIN_LOAD_LIMIT = 1000;

    public static $CONTROL_ACTION = [
        '网站流量' => [
            'default' => 'analyze/index',
            'ca' => [
                '网站流量' => 'analyze/index',
            ]
        ],
        '用户管理' => [
            'default' => 'user/index',
            'ca' => [
                '用户流量分析' => 'user/index',       //用户发送的微博、评论、点赞记录等
                '用户身份管理' => 'user/manage'       //用户身份状态的增查改
            ]
        ],
        '微博管理' => [
            'default' => 'information/manage',
            'ca' => [
                '消息管理' => 'information/manage'    //消息管理
            ]

        ]
    ];

    //redis 服务器配置
    public static $REDIS_CONFIG = [
        'url' => [
            'host' => '47.93.246.78',
            'port' => '6379'
        ],
        'auth' => [
            'user' => 'dora',
            'password' => 'dora'
        ],
        'option' => [
            'timeout' => 1,
            'pconnect' => false,
            'retry_interval' => NULL,
            'read_timeout' => 100,
        ]
    ];

    public static $MYSQL_CONFIG = [
        'url' => [
            'host' => '47.93.246.78',
            'port' => '3306'
        ],
        'auth' => [
            'user' => 'dora',
            'password' => 'Hopeforyou070301.'
        ],
        'option' => [
            'db' => 'weibo',
        ]
    ];

    public static $REDIS_KEY_NAMES = [
        'LOGIN_LOG_HASH' => 'login:log:',
        'TODAY_LOGIN_USER_HASH' => 'today:login:hash:',
        'TODAY_LOGIN_USER_LIST' => 'today:login:list'
    ];

    public static $CAN_UPLOAD_FILE_TYPE = [
        'png', 'jpg', 'jpeg', 'git'
    ];

}