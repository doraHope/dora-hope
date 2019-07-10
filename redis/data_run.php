<?php

    $redis = new Redis();
    try{
        $redis->connect('127.0.0.1', 6379, 1, NULL, 100);
    } catch (Exception $e) {
        die('redis connect fail! '.$e->getMessage().PHP_EOL);
    }
