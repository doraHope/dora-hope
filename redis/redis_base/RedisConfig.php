<?php


namespace app\redis;


class RedisConfig
{

    const REDIS_CONNECT_TIME_OUT = 3;           //超时时间
    const REDIS_CONNECT_RE_TRY_TIMEOUT = 500;   //连接失败重试延时

    const TIME_TYPE_SEC = 0;                        //时间单位为s
    const TIME_TYPE_PSEC = 1;                       //时间单位为毫秒

    const DEFAULT_EXPIRE_TIME = 30;               //默认过期时间

    const DEFAULT_DATA_STRING = '';                 //string类型默认值
    const DEFAULT_DATA_INT = 0;                     //int类型默认值

    const DEFAULT_ZINTER_OPERATION = 'sum';            //有序集合做集合运算时分值运算取的类型
    const DATA_CHANGE_TYPE_COPY = 0;                         //复制
    const DATA_CHANGE_TYPE_INCR = 1;                          //值递增
    const DATA_CHANGE_TYPE_DECR = 2;                           //值递减

}