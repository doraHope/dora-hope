<?php
/*---------------------- 
    上一篇出现的一个解决的问题是，通过在删除key的约束的时候进行版本查看，属于自己的才删除，较为有效的较少了临界资源的多个进程同时访问的问题。
    而这一篇，主要解决进程奔溃，而锁并没有设置超时时间却在崩溃前获取锁，所以在一个进程在之前流程上获取锁失败后，再判断锁是否存有生命周期，
    没有的话，就帮助锁设置其生命周期
------------------------*/


/*---------------------- 而本此修改，并不能解决操作超时的情况，但是可以减少多个进程同时访问临界资源的可能性*/
/**
 * @define lock的定义，对商城商品进行加索
 */

/*----------------- 常量定义*/
define('USER', 'user:');
define('INVENTORY', 'inventory:');
define('MARKET', 'market');

/*----------------- 公共方法*/
function log_($message) 
{
    printf($message.PHP_EOL);
    sleep(1);
}

function getKey($handler, $key, $value, $limited)
{
    $start_ = time();
    try{
        while(time() - $start_ < $limited) {
            if($handler->setNx($key, $value)) {
                $handler->setTimeout($key, $limited);
                return true;
            } elseif ($handler->ttl()) {
                //这里书上仅是对之前没有生命周期的key添加了生命周期，而没有看作是有效的获取，至于此情况下为什么不视为成功获取key，因为这种情况下与涉及临界资源的争用问题，呵呵~ 书的作者nice!
                $handler->setTimeout($key, $limited);
            }
            usleep(100);
        }
        return false;
    } catch(Exception $ex) {
        return false;
    }
}

function delKey($handler, $key, $value) 
{
    $value = $handler->get($key);
    $handler->watch($key);        //删除锁其实也是一个资源争用的过程，
    //确定这锁确实是本进程的 
    $ret = $handler->get($key);
    if($ret === $value) {
        $handler->multi();   
        $handler->delete($key);
        $handler->exec();
    }
    $handler->unwatch();
}

function can_buy($handler, $buyer, $item)
{
    $isItem = $handler->zScore(MARKET, $item);
    if(false === $isItem) {
        return false;
    }
    $money = $isItem;  
    $bar = $handler->hget(USER.$buyer, 'funds');
    if($bar < $money) {
        return false;
    }
    return $money;
}

function can_sell($handler, $user, $item)
{
    return $handler->sIsMember(INVENTORY.$user, $item);
}

/*----------------- 使用watch来实现加锁的功能*/
    $redis = new Redis();
    try{
        $redis->connect('127.0.0.1', 6379, 1, NULL, 100);
    } catch (Exception $e) {
        die('redis connect fail! '.$e->getMessage().PHP_EOL);
    }
/**
 * @scene: 在一个商城中有卖家出售的商品，其中买家拥有两个数据结构，一个是hash作为玩家身份的
 *         ，一个是set存储用户拥有的商品，在这个商城系统中，用户既可以作为买家又可以成为买家
 * @todo:  利用watch实现在用户a，b进行商城出售或者购物时，能够实现事务的一致性、完整性、原子性、永久性
 */
    
    list('u' => $user, 'a' => $action, 'i' => $item) = getopt("u:a:i:n");
    switch($action) {
        case 'b':   //买家逻辑
        {
            $seller = (explode('.', $item))[1];
            if(!getKey($redis, $item, $user, 3)) {
                //log 1-- [购买操作] 超时
                log_('[购买操作] 超时');
                break;
            }
            $redis->watch([USER.$user]);
            //在正式进入购买之前，前提条件有二： 商城中的商品确实存在；钱包中有足够的钱进行购买
            $ret = can_buy($redis, $user, $item);
            if($ret) {
                try{
                    $redis->multi();
                    //利用打日志的过程，跟踪这个操作流程
                    //log 1-- [购买操作] 开始
                    log_('[购买操作] 开始');
                    $redis->hIncrBy(USER.$seller, 'funds', $ret);           //将商品的钱打入卖家钱包
                    $redis->zRem(MARKET, $item);                            //将商品从商城中移除
                    //log 2-- [购买操作] 卖家以收款，商城出货
                    log_('[购买操作] 卖家以收款，商城出货');
                    $redis->hIncrBy(USER.$user, 'funds', -intval($ret));    //将购买商品的钱从买家钱包中划走
                    $redis->sadd(INVENTORY.$user, explode('.', $item)[0]);  //将商品打入买家的仓库
                    log_('[购买操作] 卖家以付款，商品进入买家仓库');
                    //log 3-- [购买操作] 卖家以付款，商品进入买家仓库
                    $ret = $redis->exec();
                    delKey($redis, $item, $user);
                    if(false === $ret) {
                        log_('[购买操作] 中断');
                    } else {
                        log_('[购买操作] 完成');
                    }
                    
                    //log 4-- [购买操作完成]
                } catch(Exception $ex) {
                    //log 1-- [买入失败]
                    log_('[买入失败]');
                }
                
            } else {
                //log 1-- [购买失败] 商城中商品不存在或者用户钱包余额不足 
                log_('[购买失败] 商城中商品不存在或者用户钱包余额不足 ');
            }
        }
        break;
        case 'p':   //卖家逻辑
        {
            $price = 99;
            $put = $item.'.'.$user;                //当为卖家执行时，传入参数仅为商品代号
            if(!getKey($redis, INVENTORY.'lock:'.$put, $user, 3)) {
                log_('[出售操作] 超时');
                break;
            }
            /**
             * @why: 买家为什么还需要watch自己的钱包，而卖家则取消了对自己仓库的watch，
             *       买家的购买操作成功的前提有二，而卖家上传商品到上传的前提仅有一，getKey就已经将自己仓库的物品的操作用锁限制了
             */
            // $redis->watch([INVENTORY.$user]);
            //正式出售商品前的逻辑有一(因为暂时考虑商城中可能出现同名商品)：卖家仓库中是否有该商品
            $ret = can_sell($redis, $user, $item);
            if($ret) {
                try{
                    $redis->multi();
                    //log 1-- [出售操作] 开始 $item
                    log_('[出售操作] 开始 $item[出售操作] 开始');
                    $redis->zAdd(MARKET, $price, $put);    //将商品添加到商城
                    //log 2-- [商品出售] 商品进入商城
                    log_('[商品出售] 商品进入商城');
                    $redis->sRem(INVENTORY.$user, $item);   //将商品从商城中移除
                    //log 3-- [商品出售] 商品从卖家仓库中划去
                    log_('[商品出售] 商品从卖家仓库中划去');
                    $ret = $redis->exec();
                    delKey($redis, INVENTORY.'lock:'.$put, $user);
                    //log 4-- [商品出售]
                    if(false === $ret) {
                        log_('[商品出售] 出售中断');
                    } else {
                        log_('[商品出售] 出售完成');
                    }
                    
                } catch(Exception $ex) {
                    //log 1-- [出售失败]
                    log_('[出售失败]');
                }
                
            }  else {
                //log 1-- [出售失败] 用户仓库中并不存在此商品
                log_('[出售失败] 用户仓库中并不存在此商品');
            }
        }
        break;
    }