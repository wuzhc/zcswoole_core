<?php
/**
 * Created by PhpStorm.
 * User: wuzhc
 * Date: 18-8-31
 * Time: 下午7:45
 */

namespace zcswoole\services;


class RedisDB
{
    protected $redis;
    public $host = '127.0.0.1';
    public $port = 6379;

    /**
     * @return null|\Redis
     */
    public static function getConnection()
    {
        try {
            $redis = new \Redis();
            $redis->connect('127.0.0.1', '6379');
            return $redis;
        } catch (\Error $e) {
            echo $e->getMessage() .  "\r\n";
            return null;
        }
    }
}