<?php

//一般数值常量
define('NULL_THING', -1);       //在int中代表无意义值
define('INT_DEFAULT', 0);       //int默认值

define('TYPE_INT', 0);
define('TYPE_STRING', 1);

define('FAIL', 1);
define('SUCCESS', 0);

define('WB_URL', 'http://zc.weibo.com');
define('WB_BASE', dirname(__DIR__));

define('SALT', 'hope_for_you');

/*------------------- 微博上传文件存放路径*/
define('WB_MESSAGE_SAVE_PATH', WB_BASE.'wb'.DIRECTORY_SEPARATOR);

/*------------------- 微博消息类型*/
define('TYPE_FILE', 1);
define('TYPE_TEXT', 0);

define('WB_FILE_DIR_MOD_NUMBER', 256);          //存放微博信息的文件夹

/*-------------------- 文件属性*/
define('FILE_SIZE_MIN', 1024*1);
define('FILE_SIZE_MAX', 1024*1024*2);


/*------------------- 微博消息相关*/
define('QUICK_SLOW_LIMIT_LIEN', 1000);          //区分热门微博和不热门微博的基本依据(发送者粉丝数量)
define('WB_PUSH_LENGTH', 1000);                    //每一次推送给粉丝的数量

/*-------------------- 微博redis的key*/
define('WB_FENS', 'wb:fens:liu:');              //博主的粉丝集合
define('WB_FENS_ZET', 'wb:fens:');              //博主的粉丝有序集合
define('WB_SELF', 'wb:event:self:');            //博主自己的微博消息队列
define('WB_PULIBC', 'wb:event:public:');        //博主自己+关注者发送的微博
define('WB_FOCUS', 'wb:event:other:');          //粉丝的微博消息队列


/*-------------------- redis对列名*/
define('WB_MQ_PUSH_QUICK_LENGTH', 16);           //需要紧急处理的消息队列长度
define('WB_MQ_PUSH_QUICK', 'wb:mq:quick:');     //需要紧急处理的消息队列
define('WB_MQ_PUSH_SLOW_LENGTH', 4);            //不需要紧急处理的消息队列长度
define('WB_MQ_PUSH_SLOW', 'wb:mq:slow:');       //不需要紧急处理的消息队列
define('WB_EVENT', 'wb:member:');               //用户的事件消息列表
define('WB_MAP_NAME2ID', 'wb:map:name2id');    //用户名称到用户内部id的映射

define('EVENT_PUSH_UP_LIMIT', 8);
define('REGISTER_CODE', 'wb:register:');
define('REGISTER_SUCC', 'wb:register:succ:');
/*--------------------- 消息事件*/
define('WB_EVENT_WEI_BO', 1);       //微博
define('WB_EVENT_COMMENT', 2);      //评论
define('WB_EVENT_REPLY', 3);        //回复
define('WB_EVENT_LIKE', 4);         //点赞